<?php
/**
 * This file is part of PMG\Queue
 *
 * Copyright (c) PMG <https://www.pmg.com>
 *
 * For full copyright information see the LICENSE file distributed
 * with this source code.
 *
 * @license     http://opensource.org/licenses/MIT MIT
 */

namespace PMG\Queue\Resolver;

use PMG\Queue\HandlerResolver;
use PMG\Queue\Message;
use PMG\Queue\Exception\HandlerNotFound;
use PMG\Queue\Exception\InvalidHandler;

/**
 * A simple resolver that maps messages => handlers via an array.
 *
 * @since   2.0
 */
final class SimpleResolver implements HandlerResolver
{
    /**
     * @var array
     */
    private $handlers;

    public function __construct(array $handlers)
    {
        $this->handlers = $handlers;
    }

    /**
     * {@inheritdoc}
     */
    public function handlerFor(Message $message)
    {
        $name = $message->getName();
        $handler = isset($this->handlers[$name]) ? $this->handlers[$name] : null;

        if (!$handler) {
            throw new HandlerNotFound(sprintf('No handler found for "%s"', $name));
        }

        if (!is_callable($handler)) {
            throw new InvalidHandler(sprintf('Handler for "%s" is not callable', $name));
        }

        return $handler;
    }
}
