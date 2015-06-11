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

namespace PMG\Queue\Queue;

use PMG\Queue\Message;
use PMG\Queue\DefaultEnvelope;
use PMG\Queue\RetrySpec;

/**
 * A Queue implementation that only keeps things in memory.
 *
 * @since   2.0
 */
final class MemoryQueue extends AbstractQueue implements \Countable
{
    private $queue;
    private $messageToEnvelop;

    public function __construct(RetrySpec $retries=null)
    {
        parent::__construct($retries);
        $this->queue = new \SplQueue();
        $this->messageToEnvelope = new \SplObjectStorage();
    }

    /**
     * {@inheritdoc}
     */
    public function enqueue(Message $message)
    {
        $this->queue->enqueue(new DefaultEnvelope($message));
    }

    /**
     * {@inheritdoc}
     */
    public function dequeue()
    {
        try {
            $env = $this->queue->dequeue();
            $message = $env->unwrap();
            $this->messageToEnvelope->attach($message, $env);
            return $message;
        } catch (\RuntimeException $e) {
            return null;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function ack(Message $message)
    {
        $this->detachMessage($message);
    }

    /**
     * {@inheritdoc}
     */
    public function fail(Message $message)
    {
        $env = isset($this->messageToEnvelope[$message]) ?
                $this->messageToEnvelope[$message] :
                new DefaultEnvelope($message);

        $this->detachMessage($message);

        if ($this->canRetry($env)) {
            $this->queue->enqueue($env->retry());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        return $this->queue->count();
    }

    private function detachMessage(Message $message)
    {
        $this->messageToEnvelope->detach($message);
    }
}
