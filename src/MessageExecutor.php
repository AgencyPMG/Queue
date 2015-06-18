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
 * Executors locate handlers for messages and run them.
 *
 * @since   2.0
 * @api
 */
interface MessageExecutor
{
    /**
     * Execute a single message.
     *
     * @param   $message The message to execute
     * @throws  QueueException if something is wrong internally
     * @return  boolean True if the message was successfully executed.
     */
    public function execute(Message $message);
}
