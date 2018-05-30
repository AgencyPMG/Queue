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

namespace PMG\Queue\Driver;

use PMG\Queue\Envelope;
use PMG\Queue\Message;
use PMG\Queue\DefaultEnvelope;

/**
 * A driver that keeps jobs in memory.
 *
 * @since   2.0
 */
final class MemoryDriver implements \PMG\Queue\Driver
{
    /**
     * @var     SplQueue[]
     */
    private $queues = [];

    /**
     * {@inheritdoc}
     */
    public function enqueue(string $queueName, Message $message) : Envelope
    {
        $e = new DefaultEnvelope($message);
        $this->enqueueEnvelope($queueName, $e);
        return $e;
    }

    /**
     * {@inheritdoc}
     */
    public function dequeue(string $queueName)
    {
        try{
            return $this->getQueue($queueName)->dequeue();
        } catch (\RuntimeException $e) {
            return null;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function ack(string $queueName, Envelope $envelope)
    {
        // noop
    }

    /**
     * {@inheritdoc}
     */
    public function retry(string $queueName, Envelope $envelope, int $delay=0) : Envelope
    {
        $e = $envelope->retry();
        $this->enqueueEnvelope($queueName, $e);
        return $e;
    }

    /**
     * {@inheritdoc}
     */
    public function fail(string $queueName, Envelope $envelope)
    {
        // noop
    }

    /**
     * {@inheritdoc}
     */
    public function release(string $queueName, Envelope $envelope)
    {
        $this->enqueueEnvelope($queueName, $envelope);
    }

    private function enqueueEnvelope(string $queueName, Envelope $envelope)
    {
        $this->getQueue($queueName)->enqueue($envelope);
    }

    private function getQueue(string $queueName)
    {
        if (!isset($this->queues[$queueName])) {
            $this->queues[$queueName] = new \SplQueue();
        }

        return $this->queues[$queueName];
    }
}
