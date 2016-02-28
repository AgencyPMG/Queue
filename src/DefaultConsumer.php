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
use Psr\Log\NullLogger;

/**
 * A default implementation of `Consumer`. Runs jobs via an executor.
 *
 * @since   2.0
 * @api
 */
final class DefaultConsumer implements Consumer
{
    const EXIT_ERROR = 2;

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

    /**
     * @var int
     */
    private $exitCode = 0;

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
            try {
                $this->once($queueName);
            } catch (Exception\MustStop $e) {
                $this->logger->warning('Caught a must stop exception, exiting: {msg}', [
                    'msg'   => $e->getMessage(),
                ]);
                $this->stop();
                $this->exitCode = $e->getCode();
            } catch (\Exception $e)  {
                // likely means means something went wrong with the driver
                $this->logger->emergency('Caught an unexpected {cls} exception, exiting: {msg}', [
                    'cls' => get_class($e),
                    'msg' => $e->getMessage(),
                ]);
                $this->stop();
                $this->exitCode = self::EXIT_ERROR;
            }
        }

        return $this->exitCode;
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

        $this->logger->debug('Executing message {msg}', ['msg' => $message->getName()]);
        $result = $this->executeMessage($message);
        $this->logger->debug('Executed message {msg}', ['msg' => $message->getName()]);

        if ($result) {
            $this->driver->ack($queueName, $envelope);
            $this->logger->debug('Acknowledged message {msg}', ['msg' => $message->getName()]);
        } else {
            $this->failed($queueName, $envelope);
            $this->logger->debug('Failed message {msg}', ['msg' => $message->getName()]);
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function stop()
    {
        $this->running = false;
    }

    private function failed($queueName, Envelope $env)
    {
        if ($this->retries->canRetry($env)) {
            $this->driver->retry($queueName, $env);
        } else {
            $this->driver->fail($queueName, $env);
        }
    }

    private function executeMessage(Message $message)
    {
        try {
            return $this->executor->execute($message);
        } catch (Exception\MustStop $e) {
            // MustStop exceptions are thrown by handlers to indicate a
            // graceful stop is required. So we don't wrap them. Just rethrow
            throw $e;
        } catch (\Exception $e) {
            // any other exception is simply logged. We and marked as failed
            // below. We only log here because we can't make guarantees about
            // the implementation of the executor and whether or not it actually
            // throws exceptions on failure (see ForkingExecutor).
            $this->logger->critical('Unexpected {cls} exception handling {name} message: {msg}', [
                'cls'   => get_class($e),
                'name'  => $message->getName(),
                'msg'   => $e->getMessage()
            ]);
            return false;
        }
    }
}
