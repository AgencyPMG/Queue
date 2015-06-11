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
     * @var QueueFactory
     */
    private $queues;

    /**
     * @var MessageExecutor
     */
    private $executor;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var boolean
     */
    private $running = false;

    public function __construct(QueueFactory $queues, MessageExecutor $executor, LoggerInterface $logger=null)
    {
        $this->queues = $queues;
        $this->executor = $executor;
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
        $queue = $this->queues->forName($queueName);
        $message = $queue->dequeue();
        if (!$message) {
            return;
        }

        try {
            $this->logger->debug('Executing message {msg}', ['msg' => $message->getName()]);
            $result = $this->executor->execute($message);
            $this->logger->debug('Executed message {msg}', ['msg' => $message->getName()]);
        } catch (Exception\MustStop $e) {
            $queue->fail($message);
            throw $e; // never wrap MustStop exceptions
        } catch (\Exception $e) {
            $queue->fail($message);
            throw new Exception\MessageFailed($e, $message);
        }

        if ($result) {
            $queue->ack($message);
            $this->logger->debug('Acknowledged message {msg}', ['msg' => $message->getName()]);
        } else {
            $queue->fail($message);
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
        } catch (Exception\MustStop $e) {
            $this->logger->critical('Received must quit exception, stopping: {msg}', [
                'msg' => $e->getMessage(),
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
}
