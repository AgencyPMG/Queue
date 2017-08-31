<?php declare(strict_types=1);

/**
 * This file is part of PMG\Queue
 *
 * Copyright (c) PMG <https://www.pmg.com>
 *
 * For full copyright information see the LICENSE file distributed
 * with this source code.
 *
 * @license     http://opensource.org/licenses/Apache-2.0 Apache-2.0
 */

namespace PMG\Queue;

use Psr\Log\LoggerInterface;

/**
 * A default implementation of `Consumer`. Runs jobs via a MessageHandler.
 *
 * @since   2.0
 * @api
 */
class DefaultConsumer extends AbstractConsumer
{
    /**
     * @var Driver
     */
    private $driver;

    /**
     * @var MessageHandler
     */
    private $handler;

    /**
     * @var RetrySpec
     */
    private $retries;

    /**
     * @var array
     */
    private $handlerOptions = [];

    public function __construct(
        Driver $driver,
        MessageHandler $handler,
        RetrySpec $retries=null,
        LoggerInterface $logger=null
    ) {
        parent::__construct($logger);
        $this->driver = $driver;
        $this->handler = $handler;
        $this->retries = $retries ?: new Retry\LimitedSpec();
    }

    /**
     * {@inheritdoc}
     */
    public function once($queueName, MessageLifecycle $lifecycle=null)
    {
        $envelope = $this->driver->dequeue($queueName);
        if (!$envelope) {
            return null;
        }

        $lifecycle = $lifecycle ?? new NullLifecycle();
        $message = $envelope->unwrap();

        $lifecycle->starting($message, $this);

        list($succeeded, $willRetry) = $this->doOnce($queueName, $envelope);

        $lifecycle->completed($message, $this);

        if ($succeeded) {
            $lifecycle->succeeded($message, $this);
        } else {
            $lifecycle->failed($message, $this, $willRetry);
        }

        return $succeeded;
    }

    /**
     * Do the actual work for processing a single message.
     *
     * @param $queueName The queue to which the message belongs
     * @param $envelope The envelope containing the message to process
     * @return An array if [$messageSucceeded, $willRetry]
     */
    protected function doOnce(string $queueName, Envelope $envelope) : array
    {
        $result = false;
        $message = $envelope->unwrap();

        $this->getLogger()->debug('Handling message {msg}', ['msg' => $message->getName()]);
        try {
            $result = $this->handleMessage($message);
        } catch (Exception\MustStop $e) {
            $this->driver->ack($queueName, $envelope);
            throw $e;
        } catch (\Exception $e) {
            // any other exception is simply logged. We and marked as failed
            // below. We only log here because we can't make guarantees about
            // the implementation of the handler and whether or not it actually
            // throws exceptions on failure (see PcntlForkingHandler).
            $this->getLogger()->critical('Unexpected {cls} exception handling {name} message: {msg}', [
                'cls'   => get_class($e),
                'name'  => $message->getName(),
                'msg'   => $e->getMessage()
            ]);
            $result = false;
        }
        $this->getLogger()->debug('Handled message {msg}', ['msg' => $message->getName()]);

        if ($result) {
            $willRetry = false;
            $this->driver->ack($queueName, $envelope);
            $this->getLogger()->debug('Acknowledged message {msg}', ['msg' => $message->getName()]);
        } else {
            $willRetry = $this->failed($queueName, $envelope);
            $this->getLogger()->debug('Failed message {msg}', ['msg' => $message->getName()]);
        }

        return [$result, $willRetry];
    }

    /**
     * Fail the message. This will retry it if possible.
     *
     * @param string $queueName the queue from which the message originated
     * @param $env The envelope containing the message
     * @return bool True if the message will be retried.
     */
    protected function failed($queueName, Envelope $env) : bool
    {
        $retry = $this->canRetry($env);
        if ($retry) {
            $this->getDriver()->retry($queueName, $env);
        } else {
            $this->getDriver()->fail($queueName, $env);
        }

        return $retry;
    }

    protected function handleMessage(Message $message)
    {
        return $this->getHandler()->handle($message, $this->getHandlerOptions());
    }

    protected function canRetry(Envelope $env) : bool
    {
        return $this->retries->canRetry($env);
    }

    protected function getHandler()
    {
        return $this->handler;
    }

    protected function getDriver()
    {
        return $this->driver;
    }

    /**
     * Replace all the handler options.
     *
     * @param $newOptions The options to set
     * @return void
     */
    protected function setHandlerOptions(array $newOptions)
    {
        $this->handlerOptions = $newOptions;
    }

    protected function getHandlerOptions()
    {
        return $this->handlerOptions;
    }
}
