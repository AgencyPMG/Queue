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
use PMG\Queue\Exception\CouldNotFork;
use PMG\Queue\Exception\ForkedProcessFailed;

/**
 * A message handler decorator that forks a child process to handle the message.
 *
 * Use with caution, and be aware that forking will mess with things like open
 * connections and resources (sockets, files, etc). Best bet is to wrap this
 * around a `CallableHandler` and bootstrap your entire application for each
 * message handled. Or implement your own `MessageHandler` that bootstraps the
 * entire application each time.
 *
 * @since 3.0
 */
final class PcntlForkingHandler implements MessageHandler
{
    /**
     * @var MessageHandler
     */
    private $wrapped;

    /**
     * @var Pcntl
     */
    private $pcntl;

    public function __construct(MessageHandler $wrapped, Pcntl $pcntl=null)
    {
        $this->wrapped = $wrapped;
        $this->pcntl = $pcntl ?: new Pcntl();
    }

    /**
     * {@inheritdoc}
     * This does not really deal with or log exceptions. It just swallows them
     * and makes sure that the child process exits with an error (1). Should
     * you want to do any specialized logging, that should happen in the wrapped
     * `MessageHandler`. Just be sure to return `false` (the job failed) so it
     * can be retried.
     */
    public function handle(Message $message, array $options=[]) : PromiseInterface
    {
        $child = $this->fork();
        if (0 === $child) {
            try {
                $result = $this->wrapped->handle($message, $options)->wait();
            } finally {
                $this->pcntl->quit(isset($result) && $result);
            }
        }

        $promise = new Promise(function () use (&$promise, $child) {
            $succeeded = $this->pcntl->wait($child);
            if ($succeeded) {
                $promise->resolve(true);
            } else {
                $promise->reject(new ForkedProcessFailed());
            }
        });

        return $promise;
    }

    private function fork()
    {
        $child = $this->pcntl->fork();
        if (-1 === $child) {
            throw CouldNotFork::fromLastError();
        }

        return $child;
    }
}
