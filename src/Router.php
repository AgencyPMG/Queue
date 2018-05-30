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
 * Routers map messages to Queue names.
 *
 * @since   2.0
 * @api
 */
interface Router
{
    /**
     * Look up the queue for a message.
     *
     * @param   $message The message to look up.
     * @return  string|null The queue name if found or `null` otherwise.
     */
    public function queueFor(Message $message) : ?string;
}
