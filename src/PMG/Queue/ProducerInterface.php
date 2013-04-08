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
 * Producers add jobs to the queue.
 *
 * @since   0.1
 * @author  Christopher Davis <chris@pmg.co>
 */
interface ProducerInterface
{
    /**
     * Add a job to the queue.  Only one argument is required: the job name.
     *
     * @since   0.1
     * @access  public
     * @param   string $name The job name
     * @param   array $args The args to pass to `JobInterface::work` Anything
     *          that's JsonSerializable will work just fine.
     * @throws  PMG\Queue\Exception\AddJobException if something goes wrong
     * @return  boolean True on success
     */
    public function addJob($name, array $args=array());
}
