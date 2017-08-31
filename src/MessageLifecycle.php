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
 * Provides a way to hook into the life of a message as it moves through
 * a consumer.
 *
 * This provides a way to extend the lifecycle of a message without tying you
 * to a specific thing (like an event library, etc).
 *
 * @since 4.0
 */
interface MessageLifecycle
{
    /**
     * Called when a message starts its processing in the consumer.
     *
     * @param $message The message that's starting
     * @param $consumer The consumer that's doing the work
     * @return void
     */
    public function starting(Message $message, Consumer $consumer);

    /**
     * Called when a message completes regardless of whether it was successful.
     *
     * @param $message The message that completed
     * @param $consumer The consumer that did the work
     * @return void
     */
    public function completed(Message $message, Consumer $consumer);

    /**
     * Called when a message errors.
     *
     * No details about the error are provided here because consumers, specifically
     * the default consumer implementation may not have those details. For instance,
     * if a handler forks the child process will not pass any exception info up
     * to the parent. It's up to your handlers to deal with logging and accountability.
     *
     * @param $message The message that errored
     * @param $consumer The consumer that did the work
     * @param $isRetrying Whether the message is being retried.
     * @return void
     */
    public function failed(Message $message, Consumer $consumer, bool $isRetrying);

    /**
     * Called when message processing was successful.
     *
     * @param $message The message that errored
     * @param $consumer The consumer that did the work
     * @return void
     */
    public function succeeded(Message $message, Consumer $consumer);
}
