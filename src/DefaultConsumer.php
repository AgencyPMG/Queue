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

use GuzzleHttp\Promises\PromiseInterface;
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

    /**
     * The promise that's currently being handled by a consumer.
     *
     * @var PromiseInterface|null
     */
    private $currentPromise = null;

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
    public function once(string $queueName, MessageLifecycle $lifecycle=null)
    {
        $envelope = $this->driver->dequeue($queueName);
        if (!$envelope) {
            return null;
        }

        $lifecycle = $lifecycle ?? new Lifecycle\NullLifecycle();
        $message = $envelope->unwrap();

        $lifecycle->starting($message, $this);

        list($succeeded, $willRetry) = $this->doOnce($queueName, $envelope);

        $lifecycle->completed($message, $this);

        if ($succeeded) {
            $lifecycle->succeeded($message, $this);
        } elseif ($willRetry) {
            $lifecycle->retrying($message, $this);
        } else {
            $lifecycle->failed($message, $this);
        }

        return $succeeded;
    }

    /**
     * {@inheritdoc}
     */
    public function stop(int $code=null)
    {
        if ($this->currentPromise) {
            $this->currentPromise->cancel();
        }
        parent::stop($code);
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

        $this->getLogger()->debug('Handling message {msg}', ['msg' => self::nameOf($message)]);
        try {
            $result = $this->handleMessage($message);
        } catch (Exception\MustStop $e) {
            $this->driver->ack($queueName, $envelope);
            throw $e;
        } catch (Exception\ShouldReleaseMessage $e) {
            $this->driver->release($queueName, $envelope);
            $this->getLogger()->debug('Releasing message {msg} due to {cls} exception: {err}', [
                'msg' => self::nameOf($message),
                'cls' => get_class($e),
                'err' => $e->getMessage(),
            ]);
            return [$result, true];
        } catch (\Exception $e) {
            // any other exception is simply logged. We and marked as failed
            // below. We only log here because we can't make guarantees about
            // the implementation of the handler and whether or not it actually
            // throws exceptions on failure (see PcntlForkingHandler).
            $this->getLogger()->critical('Unexpected {cls} exception handling {name} message: {msg}', [
                'cls'   => get_class($e),
                'name'  => self::nameOf($message),
                'msg'   => $e->getMessage()
            ]);
            $result = false;
        }
        $this->getLogger()->debug('Handled message {msg}', ['msg' => self::nameOf($message)]);

        if ($result) {
            $willRetry = false;
            $this->driver->ack($queueName, $envelope);
            $this->getLogger()->debug('Acknowledged message {msg}', ['msg' => self::nameOf($message)]);
        } else {
            $willRetry = $this->failed($queueName, $envelope);
            $this->getLogger()->debug('Failed message {msg}', ['msg' => self::nameOf($message)]);
        }

        return [$result, $willRetry];
    }

    /**
     * Fail the message. This will retry it if possible.
     *
     * @param $queueName the queue from which the message originated
     * @param $env The envelope containing the message
     * @return bool True if the message will be retried.
     */
    protected function failed(string $queueName, Envelope $env) : bool
    {
        $retry = $this->retries->canRetry($env);
        if ($retry) {
            $delay = $this->retries->retryDelay($env);
            $this->getDriver()->retry($queueName, $env, $delay);
        } else {
            $this->getDriver()->fail($queueName, $env);
        }

        return $retry;
    }

    protected function handleMessage(Message $message)
    {
        try {
            $this->currentPromise = $this->getHandler()->handle(
                $message,
                $this->getHandlerOptions()
            );
            return $this->currentPromise->wait();
        } finally {
            $this->currentPromise = null;
        }
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
