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
     * @return  void
     */
    public function send(Message $message);
}