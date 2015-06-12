<?php
/**
 * This file is part of PMG\Queue
 *
 * Copyright (c) PMG <https://www.pmg.com>
 *
 * For full copyright information see the LICENSE file distributed
 * with this source code.
 *
 * @license     http://opensource.org/licenses/MIT MIT
 */

namespace PMG\Queue;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * A default implementation of `Consumer`. Runs jobs via an executor.
 *
 * @since   2.0
 * @api
 */
final class DefaultConsumer implements Consumer
{
    /**
     * @var Driver
     */
    private $driver;

    /**
     * @var MessageExecutor
     */
    private $executor;

    /**
     * @var RetrySpec
     */
    private $retries;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var boolean
     */
    private $running = false;

    public function __construct(
        Driver $driver,
        MessageExecutor $executor,
        RetrySpec $retries=null,
        LoggerInterface $logger=null
    ) {
        $this->driver = $driver;
        $this->executor = $executor;
        $this->retries = $retries ?: new Retry\LimitedSpec();
        $this->logger = $logger ?: new NullLogger();
    }

    /**
     * {@inheritdoc}
     */
    public function run($queueName)
    {
        $this->running = true;
        while ($this->running) {
            $this->safeOnce($queueName);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function once($queueName)
    {
        $envelope = $this->driver->dequeue($queueName);
        if (!$envelope) {
            return;
        }

        $message = $envelope->unwrap();
        try {
            $this->logger->debug('Executing message {msg}', ['msg' => $message->getName()]);
            $result = $this->executor->execute($message);
            $this->logger->debug('Executed message {msg}', ['msg' => $message->getName()]);
        } catch (Exception\DriverError $e) {
            // MustStop errors mean something is wrong with the queue's
            // underlying drivers, so we always retry.
            $this->driver->retry($queueName, $envelope);
            throw $e;
        } catch (Exception\MustStop $e) {
            // MustStop exceptions are thrown by handlers to indicate a
            // graceful stop is required. So we don't wrapped them. Just rethrow
            throw $e;
        } catch (\Exception $e) {
            $this->maybeRetry($queueName, $envelope);
            throw new Exception\MessageFailed($e, $message);
        }

        if ($result) {
            $this->driver->ack($queueName, $envelope);
            $this->logger->debug('Acknowledged message {msg}', ['msg' => $message->getName()]);
        } else {
            $this->maybeRetry($queueName, $envelope);
            $this->logger->debug('Failed message {msg}', ['msg' => $message->getName()]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function stop()
    {
        $this->running = false;
    }

    private function safeOnce($queueName)
    {
        try {
            $this->once($queueName);
        } catch (Exception\DriverError $e) {
            $this->logger->critical('Caught a {cls} Driver Error, exiting: {msg}', [
                'cls' => get_class($e),
                'msg' => $e->getMessage(),
            ]);
            $this->stop();
        } catch (Exception\MustStop $e) {
            $this->logger->warning('Caught a must stop exception, exiting: {msg}', [
                'msg'   => $e->getMessage(),
            ]);
            $this->stop();
        } catch (Exception\MessageFailed $e) {
            $this->logger->critical('Unexpected {cls} exception handling {name} message: {msg}', [
                'cls'   => get_class($e->getPrevious()),
                'name'  => $e->getQueueMessage()->getName(),
                'msg'   => $e->getMessage()
            ]);
        }
    }

    private function maybeRetry($queueName, Envelope $env)
    {
        if ($this->retries->canRetry($env)) {
            $this->driver->retry($queueName, $env);
        }
    }
}
