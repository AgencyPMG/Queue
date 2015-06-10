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
 * The central queue abstraction -- enqueue or dequeuue messages.
 *
 * @since   2.0
 * @api
 */
interface Queue
{
    /**
     * Add a new message to the queue.
     *
     * @param   $message the message to add
     * @return  void
     */
    public function enqueue(Message $message);

    /**
     * Pull a message out of the queue.
     *
     * @return  Message|null A message if one is available, null otherwise.
     */
    public function dequeue();
}
