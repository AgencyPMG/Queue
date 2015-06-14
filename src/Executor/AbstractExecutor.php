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

namespace PMG\Queue\Executor;

use PMG\Queue\HandlerResolver;
use PMG\Queue\Message;
use PMG\Queue\MessageExecutor;
use PMG\Queue\Exception\HandlerNotFound;

/**
 * ABC for executors -- all of them share the need of a `HandlerResolver`.
 *
 * @since   2.0
 */
abstract class AbstractExecutor implements MessageExecutor
{
    /**
     * @var HandlerResolver
     */
    private $resolver;

    public function __construct(HandlerResolver $resolver)
    {
        $this->resolver = $resolver;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(Message $message)
    {
        $handler = $this->handlerFor($message);
        if (!$handler) {
            throw new HandlerNotFound(sprintf('No handler found for "%s"', $message->getName()));
        }

        return $this->executeInternal($message, $handler);
    }

    /**
     * This actually executes the handler according to the executors implementation.
     *
     * @param   $message The message to be executed
     * @param   $handler The handler
     * @return  boolean True is the handler succeeds.
     */
    abstract protected function executeInternal(Message $message, callable $handler);

    protected function handlerFor(Message $message)
    {
        return $this->resolver->handlerFor($message);
    }
}
