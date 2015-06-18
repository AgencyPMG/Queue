<?php
/**
 * This file is part of PMG\Queue
 *
 * Copyright (c) 2013 PMG Worldwide
 *
 * @package     PMGQueue
 * @copyright   2013 PMG Worldwide
 * @license     http://opensource.org/licenses/Apache-2.0 Apache-2.0
 */

namespace PMG\Queue\Executor;

/**
 * A simple wrapper around the pcntl extension to help with forking.
 *
 * @since   2.0
 */
final class PcntlHelper
{
    public function fork()
    {
        return pcntl_fork();
    }

    public function wait($pid)
    {
        pcntl_waitpid($pid, $status);

        return $status;
    }

    public function getStatus($status)
    {
        if (pcntl_wifexited($status)) {
            return pcntl_wexitstatus($status);
        }

        return 1;
    }
}
