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

namespace PMG\Queue\Lifecycle;

use PMG\Queue\Consumer;
use PMG\Queue\MessageLifecycle;

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
    public function starting(object $message, Consumer $consumer) : void
    {
        // noop
    }

    /**
     * {@inheritdoc}
     */
    public function completed(object $message, Consumer $consumer) : void
    {
        // noop
    }

    /**
     * {@inheritdoc}
     */
    public function retrying(object $message, Consumer $consumer) : void
    {
        // noop
    }

    /**
     * {@inheritdoc}
     */
    public function failed(object $message, Consumer $consumer) : void
    {
        // noop
    }

    /**
     * {@inheritdoc}
     */
    public function succeeded(object $message, Consumer $consumer) : void
    {
        // noop
    }
}
