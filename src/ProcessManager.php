<?php
/**
 * This file is part of PMG\Queue
 *
 * Copyright (c) 2013 PMG Worldwide
 *
 * @package     PMGQueue
 * @copyright   2013 PMG Worldwide
 * @license     http://opensource.org/licenses/MIT MIT
 */

namespace PMG\Queue;

class ProcessManager implements ProcessManagerInterface
{
    /**
     * From ProcessManagerInterface
     *
     * {@inheritdoc}
     */
    public function fork()
    {
        if (!function_exists('pcntl_fork')) {
            return -1;
        }

        return pcntl_fork();
    }

    /**
     * From ProcessManagerInterface
     *
     * {@inheritdoc}
     */
    public function wait($pid)
    {
        pcntl_waitpid($pid, $status);

        return $status;
    }

    /**
     * From ProcessManagerInterface
     *
     * {@inheritdoc}
     */
    public function getStatus($status)
    {
        if (pcntl_wifexited($status)) {
            return pcntl_wexitstatus($status);
        }

        return 1;
    }
}
