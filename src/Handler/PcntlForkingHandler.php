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
use PMG\Queue\Exception\CouldNotFork;

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
     * This does not catch exceptions. If an exception is thrown, PHP will do its
     * own logging and exit with a 255 status code (failure) causing the parent
     * process to return `false` (the message failed). Should you want to do
     * any specialized logging, that should happen in the wrapped `MessageHandler`.
     */
    public function handle(Message $message, array $options=[])
    {
        $child = $this->fork();
        if (0 === $child) {
            $result = $this->wrapped->handle($message, $options);
            $this->pcntl->quit($result);
        }

        return $this->pcntl->wait($child);
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
