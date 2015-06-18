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

namespace PMG\Queue\Resolver;

use PMG\Queue\HandlerResolver;
use PMG\Queue\Message;
use PMG\Queue\Exception\InvalidHandler;
use PMG\Queue\Exception\InvalidArgumentException;

/**
 * A simple resolver that maps messages => handlers via an array or ArrayAccess.
 *
 * @since   2.0
 */
final class MappingResolver implements HandlerResolver
{
    /**
     * @var array|ArrayAccess
     */
    private $handlers;

    public function __construct($handlers)
    {
        if (!is_array($handlers) && !$handlers instanceof \ArrayAccess) {
            throw new InvalidArgumentException(sprintf(
                '%s expects $handers to be an array or ArrayAccess implementation, got "%s"',
                __METHOD__,
                is_object($handlers) ? get_class($handlers) : gettype($handlers)
            ));
        }

        $this->handlers = $handlers;
    }

    /**
     * {@inheritdoc}
     */
    public function handlerFor(Message $message)
    {
        $name = $message->getName();
        $handler = isset($this->handlers[$name]) ? $this->handlers[$name] : null;

        if ($handler && !is_callable($handler)) {
            throw new InvalidHandler(sprintf('Handler for "%s" is not callable', $name));
        }

        return $handler;
    }
}
