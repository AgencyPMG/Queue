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

/**
 * Defines a driver backend for persistant queues.
 *
 * Drivers make no promises or guarantees about ordering of messages. Some drivers
 * will be LIFO others will be FIFO and some have no ordering. Don't build systems
 * that depend on those charactertistics.
 *
 * Additionally, drivers know nothing about configuring a backend. If special steps
 * are required to create a queue, those should be done elsewhere.
 *
 * @since   2.0
 * @api
 */
interface Driver
{
    /**
     * Add a new message to the queue.
     *
     * @param   string $queueName The name of the queue to put the message in.
     * @param   $message The message to add.
     * @throws  Exception\DriverError when something goes wrong
     * @return  Envelope An envelop representing the message in the queue.
     */
    public function enqueue(string $queueName, Message $message) : Envelope;

    /**
     * Pull a message out of the queue.
     *
     * @param   string $queueName The queue from which to pull messages.
     * @throws  Exception\DriverError when something goes wrong
     * @return  Envelope|null An envelope if a message is found, null otherwise
     */
    public function dequeue(string $queueName);

    /**
     * Acknowledge a message as complete.
     *
     * @param   string $queueName The queue from which the message came
     * @param   $envelope The message envelop -- should be the same instance
     *          returned by `dequeue`
     * @throws  Exception\DriverError when something goes wrong
     * @return  void
     */
    public function ack(string $queueName, Envelope $envelope);

    /**
     * Retry a job -- put it back in the queue for retrying.
     *
     * @param   string $queueName The queue from whcih the message came
     * @param   $envelope The message envelope -- should be the same instance
     *          returned from `dequeue`
     * @throws  Exception\DriverError when something goes wrong
     * @return  Envelope The new envelope for the retried job.
     */
    public function retry(string $queueName, Envelope $envelope) : Envelope;

    /**
     * Fail a job -- this called when no more retries can be attempted.
     *
     * @param   string $queueName The queue from whcih the message came
     * @param   $envelope The message envelope -- should be the same instance
     *          returned from `dequeue`
     * @throws  Exception\DriverError when something goes wrong
     * @return  void
     */
    public function fail(string $queueName, Envelope $envelope);
}
