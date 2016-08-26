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

namespace PMG\Queue\Handler;

use PMG\Queue\Message;
use PMG\Queue\MessageHandler;

/**
 * A message handler that invokes a callable with the message. The callback should
 * return a boolean value indicating whether the message succeeded.
 *
 * @since 3.0
 */
final class CallableHandler implements MessageHandler
{
    /**
     * The message callback.
     *
     * @var callable
     */
    private $callback;

    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(Message $message, array $options=[])
    {
        return call_user_func($this->callback, $message, $options);
    }
}
