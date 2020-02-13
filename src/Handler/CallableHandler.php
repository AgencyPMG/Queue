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

namespace PMG\Queue\Handler;

use GuzzleHttp\Promise\Promise;
use GuzzleHttp\Promise\PromiseInterface;
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
     * This *always* resolves with the values from the callback. If the callback
     * throws something that will result in a rejected promise.
     */
    public function handle(object $message, array $options=[]) : PromiseInterface
    {
        $promise = new Promise(function () use (&$promise, $message, $options) {
            $promise->resolve(call_user_func(
                $this->callback,
                $message,
                $options
            ));
        });

        return $promise;
    }
}
