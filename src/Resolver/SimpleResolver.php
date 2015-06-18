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

/**
 * A resolver that always returns the same handler.
 *
 * @since   2.0
 */
final class SimpleResolver implements HandlerResolver
{
    /**
     * @var callable
     */
    private $handler;

    public function __construct(callable $handler)
    {
        $this->handler = $handler;
    }

    /**
     * {@inheritdoc}
     */
    public function handlerFor(Message $message)
    {
        return $this->handler;
    }
}
