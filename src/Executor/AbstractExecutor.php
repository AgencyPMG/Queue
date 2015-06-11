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

    protected function handlerFor(Message $message)
    {
        return $this->resolver->handlerFor($message);
    }
}
