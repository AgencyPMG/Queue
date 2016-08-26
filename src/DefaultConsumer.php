<?php
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
    public function once($queueName)
    {
        $envelope = $this->driver->dequeue($queueName);
        if (!$envelope) {
            return null;
        }

        $result = false;
        $message = $envelope->unwrap();

        $this->getLogger()->debug('Handling message {msg}', ['msg' => $message->getName()]);
        $result = $this->handleMessage($message);
        $this->getLogger()->debug('Handled message {msg}', ['msg' => $message->getName()]);

        if ($result) {
            $this->driver->ack($queueName, $envelope);
            $this->getLogger()->debug('Acknowledged message {msg}', ['msg' => $message->getName()]);
        } else {
            $this->failed($queueName, $envelope);
            $this->getLogger()->debug('Failed message {msg}', ['msg' => $message->getName()]);
        }

        return $result;
    }

    protected function failed($queueName, Envelope $env)
    {
        if ($this->canRetry($env)) {
            $this->getDriver()->retry($queueName, $env);
        } else {
            $this->getDriver()->fail($queueName, $env);
        }
    }

    protected function handleMessage(Message $message)
    {
        try {
            return $this->getHandler()->handle($message, $this->getHandlerOptions());
        } catch (Exception\MustStop $e) {
            // MustStop exceptions are thrown by handlers to indicate a
            // graceful stop is required. So we don't wrap them. Just rethrow
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
            return false;
        }
    }

    protected function canRetry(Envelope $env)
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
