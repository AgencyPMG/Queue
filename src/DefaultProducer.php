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

/**
 * The default implementation of the producer. Uses a router to look up
 * where the message should go then adds it to the queue.
 *
 * @since   2015-06-09
 */
final class DefaultProducer implements Producer
{
    /**
     * @var Driver
     */
    private $driver;

    /**
     * @var Router
     */
    private $router;

    public function __construct(Driver $driver, Router $router)
    {
        $this->driver = $driver;
        $this->router = $router;
    }

    /**
     * {@inheritdoc}
     */
    public function send(Message $message)
    {
        $queueName = $this->router->queueFor($message);
        if (!$queueName) {
            throw new Exception\QueueNotFound(sprintf('Could not find a queue for "%s"', $message->getName()));
        }

        $this->driver->enqueue($queueName, $message);
    }

    /**
     * {@inheritdoc}
     */
    public function broadcast(Message $message)
    {
        $this->driver->broadcast($message);
    }
}
