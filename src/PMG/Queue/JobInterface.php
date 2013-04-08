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
 * Common interface for jobs to implement.
 *
 * @since   0.1
 * @author  Christopher Davis <chris@pmg.co>
 */
interface JobInterface
{
    /**
     * Do the work.
     *
     * @since   0.1
     * @access  public
     * @param   array $args The array of args put in the queue
     * @throws  Whatever -- safe to throw, the consumer will take care of it.
     * @return  void if no exception are thrown the Consumer assumes the job
     *          was successful
     */
    public function work(array $args=array());
}
