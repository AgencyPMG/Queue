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

namespace PMG\Queue\Adapter;

/**
 * Adapters take care of the actual communication with the Queue backend which
 * maybe be beanstalkd, Redis, or whatever.
 *
 * Adapaters are in charge of keeping their state and keeping track of their
 * current job. They are not thread safe, in other words.
 *
 * @since   0.1
 * @access  public
 */
interface AdapterInterface
{
    const JOB_NAME = '__job_name';

    /**
     * Acquire a job from the queue.
     *
     * @since   0.1
     * @access  public
     * @throws  PMG\Queue\Adapater\Exception\AdapaterException if something goes wrong.
     * @return  array array($job_class, $args) pair
     */
    public function acquire();

    /**
     * Mark a job as done.
     *
     * @since   0.1
     * @access  public
     * @throws  PMG\Queue\Adapater\Exception\AdapaterException if something goes wrong.
     * @return  true on success
     */
    public function finish();

    /**
     * Put a job back into the queue -- used to indicate failure, usually.
     *
     * @since   0.1
     * @access  public
     * @throws  PMG\Queue\Adapater\Exception\AdapaterException if something goes wrong.
     * @return  true on success
     */
    public function punt();

    /**
     * Let the backend know that we're still working on the current job.
     *
     * @since   0.1
     * @access  public
     * @throws  PMG\Queue\Adapater\Exception\AdapaterException if something goes wrong.
     * @return  true on success
     */
    public function touch();

    /**
     * Put a job in the queue.
     *
     * @since   0.1
     * @access  public
     * @param   string $job_name The job's name
     * @param   array $job_body The job's body to `json_encode`
     * @param   int $ttr The time in seconds the job should be alotted before failing
     * @param   int $delay How long to delay the job before running it.
     * @throws  PMG\Queue\Adapater\Exception\AdapaterException if something goes wrong.
     * @return  true on success
     */
    public function put($job_name, array $job_body, $ttr=null, $delay=null);
}
