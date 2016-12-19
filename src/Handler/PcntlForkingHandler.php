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

    public function __construct(MessageHandler $wrapped)
    {
        // @codeCoverageIgnoreStart
        if (!function_exists('pcntl_fork')) {
            throw new \RuntimeException(sprintf('%s can only be used if the pcntl extension is loaded', __CLASS__));
        }
        // @codeCoverageIgnoreEnd

        $this->wrapped = $wrapped;
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
            exit($result ? 0 : 1);
        }

        pcntl_waitpid($child, $status, WUNTRACED);

        return $this->wasSuccessfulExit($status);
    }

    /**
     * Fork a new process. If the fork can't happen we assume something has gone
     * catastrophically wrong and throw a `RuntimeException`. This should exit
     * the parent `Consumer`.
     *
     * @return int
     */
    private function fork()
    {
        $child = @pcntl_fork();
        // @codeCoverageIgnoreStart
        if (-1 === $child) {
            throw CouldNotFork::fromLastError();
        }
        // @codeCoverageIgnoreEnd

        return $child;
    }

    private function wasSuccessfulExit($status)
    {
        return pcntl_wifexited($status) ? pcntl_wexitstatus($status) === 0 : false;
    }
}
