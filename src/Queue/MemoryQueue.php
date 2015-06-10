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

/**
 * A Queue implementation that only keeps things in memory.
 *
 * @since   2.0
 */
final class MemoryQueue implements \PMG\Queue\Queue, \Countable
{
    private $queue;

    public function __construct()
    {
        $this->queue = new \SplQueue();
    }

    /**
     * {@inheritdoc}
     */
    public function enqueue(Message $message)
    {
        $this->queue->enqueue($message);
    }

    /**
     * {@inheritdoc}
     */
    public function dequeue()
    {
        try {
            return $this->queue->dequeue();
        } catch (\RuntimeException $e) {
            return null;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        return $this->queue->count();
    }
}
