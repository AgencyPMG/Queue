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
use PMG\Queue\Exception\InvalidArgumentException;

/**
 * A `MessageLifecycle` implementation that applies other lifecycles based on
 * a apping and the incoming message name.
 *
 * @since 4.2
 */
final class MappingLifecycle implements MessageLifecycle
{
    /**
     * A mapping of message names to lifecycles. [$messageName => $lifecycle]
     *
     * @var array|ArrayAccess
     */
    private $mapping;

    /**
     * @var MessageLifecycle
     */
    private $fallback;

    /**
     * @param array|ArrayAccess $mapping the message mapping
     * @param $fallback The message lifecycle to which unmatches messages will be applied
     * @throws InvalidArgumentException if $mapping is a bad type
     */
    public function __construct($mapping, ?MessageLifecycle $fallback=null)
    {
        if (!is_array($mapping) && !$mapping instanceof \ArrayAccess) {
            throw new InvalidArgumentException(sprintf(
                '$mapping must be an array or ArrayAccess implementation, got "%s"',
                is_object($mapping) ? get_class($mapping) : gettype($mapping)
            ));
        }

        $this->mapping = $mapping;
        $this->fallback = $fallback ?? new NullLifecycle();
    }

    /**
     * {@inheritdoc}
     */
    public function starting(Message $message, Consumer $consumer)
    {
        $this->lifecycleFor($message)->starting($message, $consumer);
    }

    /**
     * {@inheritdoc}
     */
    public function completed(Message $message, Consumer $consumer)
    {
        $this->lifecycleFor($message)->completed($message, $consumer);
    }

    /**
     * {@inheritdoc}
     */
    public function failed(Message $message, Consumer $consumer, bool $isRetrying)
    {
        $this->lifecycleFor($message)->failed($message, $consumer, $isRetrying);
    }

    /**
     * {@inheritdoc}
     */
    public function succeeded(Message $message, Consumer $consumer)
    {
        $this->lifecycleFor($message)->succeeded($message, $consumer);
    }

    /**
     * Check whether or not the message has a lifecycle.
     * 
     * @param $messageName the message name to check
     * @return true if a message lifecycle exists for the message
     */
    public function has(string $messageName) : bool
    {
        return isset($this->mapping[$messageName]);
    }

    private function lifecycleFor(Message $message) : MessageLifecycle
    {
        $name = $message->getName();
        return $this->has($name) ? $this->mapping[$name] : $this->fallback;
    }
}
