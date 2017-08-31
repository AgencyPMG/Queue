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
 * A `MessageLifecycle` implementation that does nothing.
 *
 * This is also useful to extend in your own implementation if you only care
 * about certain events.
 *
 * @since 4.0
 */
class NullLifecycle implements MessageLifecycle
{
    /**
     * {@inheritdoc}
     */
    public function starting(Message $message, Consumer $consumer)
    {
        // noop
    }

    /**
     * {@inheritdoc}
     */
    public function completed(Message $message, Consumer $consumer)
    {
        // noop
    }

    /**
     * {@inheritdoc}
     */
    public function failed(Message $message, Consumer $consumer, bool $isRetrying)
    {
        // noop
    }

    /**
     * {@inheritdoc}
     */
    public function succeeded(Message $message, Consumer $consumer)
    {
        // noop
    }
}
