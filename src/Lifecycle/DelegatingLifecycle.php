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
 * A `MessageLifecycle` implementation that delegates to other lifecycles.
 *
 * @since 4.2
 */
final class DelegatingLifecycle implements MessageLifecycle, \Countable
{
    /**
     * @var MessageLifecycle[]
     */
    private $lifecycles;

    public function __construct(MessageLifecycle ...$lifecycles)
    {
        $this->lifecycles = $lifecycles;
    }

    public static function fromArray(array $lifecycles) : self
    {
        @trigger_error(sprintf(
            '%s is deprecated as of version 5.0 and will be removed in 6.0, use %s::fromIterable instead',
            __METHOD__,
            __CLASS__
        ), E_USER_DEPRECATED);

        return new self(...$lifecycles);
    }

    public static function fromIterable(iterable $lifecycles) : self
    {
        return new self(...$lifecycles);
    }

    /**
     * {@inheritdoc}
     */
    public function starting(object $message, Consumer $consumer)
    {
        $this->apply(function (MessageLifecycle $ml) use ($message, $consumer) {
            $ml->starting($message, $consumer);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function completed(object $message, Consumer $consumer)
    {
        $this->apply(function (MessageLifecycle $ml) use ($message, $consumer) {
            $ml->completed($message, $consumer);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function retrying(object $message, Consumer $consumer)
    {
        $this->apply(function (MessageLifecycle $ml) use ($message, $consumer) {
            $ml->retrying($message, $consumer);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function failed(object $message, Consumer $consumer)
    {
        $this->apply(function (MessageLifecycle $ml) use ($message, $consumer) {
            $ml->failed($message, $consumer);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function succeeded(object $message, Consumer $consumer)
    {
        $this->apply(function (MessageLifecycle $ml) use ($message, $consumer) {
            $ml->succeeded($message, $consumer);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        return count($this->lifecycles);
    }

    private function apply(callable $fn)
    {
        foreach ($this->lifecycles as $lifecycle) {
            $fn($lifecycle);
        }
    }
}
