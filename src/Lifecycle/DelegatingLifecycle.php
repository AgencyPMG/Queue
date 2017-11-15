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
use PMG\Queue\Message;
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
        return new self(...$lifecycles);
    }

    /**
     * {@inheritdoc}
     */
    public function starting(Message $message, Consumer $consumer)
    {
        $this->apply(function (MessageLifecycle $ml) use ($message, $consumer) {
            $ml->starting($message, $consumer);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function completed(Message $message, Consumer $consumer)
    {
        $this->apply(function (MessageLifecycle $ml) use ($message, $consumer) {
            $ml->completed($message, $consumer);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function failed(Message $message, Consumer $consumer, bool $isRetrying)
    {
        $this->apply(function (MessageLifecycle $ml) use ($message, $consumer, $isRetrying) {
            $ml->failed($message, $consumer, $isRetrying);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function succeeded(Message $message, Consumer $consumer)
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

    private function apply(callable $fn) : void
    {
        foreach ($this->lifecycles as $lifecycle) {
            $fn($lifecycle);
        }
    }
}
