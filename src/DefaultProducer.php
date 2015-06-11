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

/**
 * The default implementation of the producer. Uses a router to look up
 * where the message should go then adds it to the queue.
 *
 * @since   2015-06-09
 */
final class DefaultProducer implements Producer
{
    /**
     * @var Router
     */
    private $router;

    /**
     * @var QueueFactory
     */
    private $queues;

    public function __construct(Router $router, QueueFactory $queues)
    {
        $this->router = $router;
        $this->queues = $queues;
    }

    /**
     * {@inheritdoc}
     */
    public function send(Message $message)
    {
        $queueName = $this->router->queueFor($message);
        $this->queues->forName($queueName)->enqueue($message);
    }
}
