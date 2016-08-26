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
     * @return boolean True if the message was handled successfully. False otherwise.
     *         we return a value here so message handlers aren't limited to processing
     *         in the same thread/process. Eg. a handler could fork and fail a message
     *         but the exception thrown in a child process wouldn't make it to the
     *         parent.
     */
    public function handle(Message $message, array $options=[]);
}
