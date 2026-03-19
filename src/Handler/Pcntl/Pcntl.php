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

namespace PMG\Queue\Handler\Pcntl;

use PMG\Queue\Exception\AbnormalExit;

/**
 * A very thin wrapper around the `pcntl_*` functions and `exit` to deal
 * with forking processes. This exists simply so we can mock it and validate that
 * `PcntlForkingHandler` works.
 *
 * @since 3.1
 * @internal
 */
class Pcntl
{
    public function __construct()
    {
        // @codeCoverageIgnoreStart
        if (!function_exists('pcntl_fork')) {
            throw new \RuntimeException(sprintf('%s can only be used if the pcntl extension is loaded', __CLASS__));
        }
        // @codeCoverageIgnoreEnd
    }

    /**
     * Fork a new process using `pcntl_fork()`.
     *
     * @return int The child PID in the parent process, `0` in the child
     *             process, or `-1` on failure.
     */
    public function fork() : int
    {
        return @pcntl_fork();
    }

    /**
     * Wait for the child process to finish and report whether it exited
     * successfully. If the child process exits normally, this returns a
     * result object. If there was a non-normal exit (like a segfault), this
     * will throw.
     *
     * @return WaitResult The child process exit result.
     */
    public function wait($child) : WaitResult
    {
        pcntl_waitpid($child, $status, WUNTRACED);

        if (pcntl_wifexited($status)) {
            return new WaitResult(pcntl_wexitstatus($status));
        }

        throw AbnormalExit::fromWaitStatus($status);
    }

    /**
     * Quit the current process by calling `exit`. The exit code is defined by
     * whether the `$successful` value is true.
     *
     * @param bool $successful If true `exit(0)` otherwise `exit(1)`
     * @return void
     */
    public function quit($successful) : void
    {
        exit($successful ? 0 : 1);
    }

    /**
     * Deliver a signal to a process.
     *
     * @param $child The process to signal
     * @param $sig The signal to send.
     * @return void
     */
    public function signal(int $child, int $sig) : void
    {
        posix_kill($child, $sig);
    }
}
