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

use PMG\Queue\Exception\AbnormalExit;

/**
 * A very thing wrapper around the the `pcntl_*` functions and `exit` to deal
 * with forking processes. This exists simply so we can mock it and validate that
 * `PcntlForkingHandler` works.
 *
 * @since 3.1
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
     * Fork a new process and return the current processes ID. In the parent thead
     * this will be the child process' ID and the child thread will see a `0`.
     *
     * @throws CouldNotFork if the call to `pcntl_fork` fails.
     * @return int
     */
    public function fork()
    {
        return @pcntl_fork();
    }

    /**
     * Wait for the child process to finish and report wether its exit status
     * was successful or not. If the child process exits normally this is
     * will return a bool. If there was a non-normal exit (like a segfault)
     * this will throw.
     *
     * @return bool True if the child existed successfully.
     */
    public function wait($child)
    {
        pcntl_waitpid($child, $status, WUNTRACED);

        if (pcntl_wifexited($status)) {
            return pcntl_wexitstatus($status) === 0;
        }

        throw AbnormalExit::fromWaitStatus($status);
    }

    /**
     * Quit the current process by calling `exit`. The exit code is defined by
     * whather the $succesful value is true.
     *
     * @param bool $successful If true `exit(0)` otherwise `exit(1)`
     * @return void
     */
    public function quit($successful)
    {
        exit($successful ? 0 : 1);
    }
}
