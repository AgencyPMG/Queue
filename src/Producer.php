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
 * Producers push messages into the queue.
 *
 * @since   2.0
 * @api
 */
interface Producer
{
    /**
     * Send a new message into the queue.
     *
     * @param   $message The message to send
     * @throws  Exception\DriverError if something goes wrong with the
     *          queue backend.
     * @throws  Exception\QueueNotFound if the router fails to find a queue.
     * @return  void
     */
    public function send(Message $message);
}
