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

use GuzzleHttp\Promise\PromiseInterface;

/**
 * Something that can process a message.
 *
 * @since 3.0
 */
interface MessageHandler
{
    /**
     * Handle a message. What that means depends on the implementation, but it
     * probably means interact with the user's system based on the given message.
     *
     * @param $message That message to process.
     * @param array $options A freeform set of options that may be passed from the
     *        consumer.
     * @return a promise object that resolves to `true` if the the handler was successful.
     *         or false if the handler failed. Since handlers may process messages
     *         outside the current thread, we're limited to a boolean here.
     */
    public function handle(Message $message, array $options=[]) : PromiseInterface;
}
