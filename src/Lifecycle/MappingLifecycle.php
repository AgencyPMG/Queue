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
use PMG\Queue\MessageNames;
use PMG\Queue\Exception\InvalidArgumentException;

/**
 * A `MessageLifecycle` implementation that applies other lifecycles based on
 * a apping and the incoming message name.
 *
 * @since 4.2
 */
final class MappingLifecycle implements MessageLifecycle
{
    use MessageNames;

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
    public function __construct($mapping, MessageLifecycle $fallback=null)
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
    public function starting(object $message, Consumer $consumer) : void
    {
        $this->lifecycleFor($message)->starting($message, $consumer);
    }

    /**
     * {@inheritdoc}
     */
    public function completed(object $message, Consumer $consumer) : void
    {
        $this->lifecycleFor($message)->completed($message, $consumer);
    }

    /**
     * {@inheritdoc}
     */
    public function retrying(object $message, Consumer $consumer) : void
    {
        $this->lifecycleFor($message)->retrying($message, $consumer);
    }

    /**
     * {@inheritdoc}
     */
    public function failed(object $message, Consumer $consumer) : void
    {
        $this->lifecycleFor($message)->failed($message, $consumer);
    }

    /**
     * {@inheritdoc}
     */
    public function succeeded(object $message, Consumer $consumer) : void
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

    private function lifecycleFor(object $message) : MessageLifecycle
    {
        $name = self::nameOf($message);
        return $this->has($name) ? $this->mapping[$name] : $this->fallback;
    }
}
