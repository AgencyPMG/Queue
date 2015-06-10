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

namespace PMG\Queue\Router;

use PMG\Queue\Message;

/**
 * Routers map messages to Queue names.
 *
 * @since   2.0
 */
interface Router
{
    /**
     * Look up the queue for a message.
     *
     * @param   $message The message to look up.
     * @return  string|null The queue name if found or `null` otherwise.
     */
    public function queueFor(Message $message);
}
