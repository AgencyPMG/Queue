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

/**
 * A wrapper around pcntl_* functions to make testing a bit easier.
 *
 * @since   0.1
 */
interface ProcessManagerInterface
{
    /**
     * Fork a new process.
     *
     * @since   0.1
     * @access  public
     * @return  int -1 on failure
     */
    public function fork();

    /**
     * Wait (block) until a child thread ($pid) exits.
     *
     * @since   0.1
     * @access  public
     * @param   int $pid
     * @return  int
     */
    public function wait($pid);

    /**
     * Get the status code form an exit
     *
     * @since   0.1
     * @access  public
     * @param   int $status
     * @return  int
     */
    public function getStatus($status);
}
