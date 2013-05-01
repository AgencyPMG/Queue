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
 * Consumer take jobs from the task queue and work with them.
 *
 * Jobs MUST be "whitelisted" with the consumer using the `whitelistJob` method.
 *
 * @since   0.1
 * @author  Christopher Davis <chris@pmg.co>
 */
interface ConsumerInterface
{
    /**
     * Whitelist a job for use. Non-whitelisted jobs will simply be discarded.
     *
     * @since   0.1
     * @access  public
     * @param   string $name The job name (the $name argument
     *          from ProducerInterface::addJob)
     * @param   string $job_class A class that implements JobInterface to be
     *          instantiated to run the job.
     * @param   array $constructor An array of args to pass to the job constructor
     * @return  void
     */
    public function whitelistJob($name, $job_class);

    /**
     * Remove a job from the whitelist.
     *
     * @since   0.1
     * @access  public
     * @param   string $name
     * @return  boolean
     */
    public function blacklistJob($name);

    /**
     * Run the consumer.
     *
     * Example:
     *      $consumer = new Consumer;
     *      try {
     *          $consumer->run();
     *      } catch (\PMG\Queue\Exception\ConsumerException $e) {
     *          // we had to exit. Try to recover or alert admins or whatever
     *          exit($e->getCode());
     *      }
     *
     * @since   0.1
     * @access  public
     * @throws  PMG\Queue\Exception\ConsumerException if something goes wrong
     * @return  void
     */
    public function run();

    /**
     * Do a single job then exit.
     *
     * Example:
     *      $consumer = new Consumer;
     *      try {
     *          $consumer->runOnce();
     *      } catch (\PMG\Queue\Exception\ConsumerException $e) {
     *          // we had to exit. Try to recover or alert admins or whatever
     *          exit($e->getCode());
     *      }
     *
     * @since   0.1
     * @access  public
     * @throws  PMG\Queue\Exception\ConsumerException
     * @return  int 0 on success, something other than zero on failure -- mimics
     *          unix-like exit codes, in other words.
     */
    public function runOnce();
}
