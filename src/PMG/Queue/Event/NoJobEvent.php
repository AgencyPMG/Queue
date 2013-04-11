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

namespace PMG\Queue\Event;

/**
 * Fired when a job is not whitelisted for consumption by the consumer
 *
 * @since   0.1
 * @author  Christopher Davis <chris@pmg.co>
 */
class NoJobEvent extends \Symfony\Component\EventDispatcher\Event
{
    private $job_name;
    private $job_args;

    public function __construct($job_name, $job_args)
    {
        $this->setJobName($job_name);
        $this->setJobArgs($job_args);
    }

    public function getJobName()
    {
        return $this->job_name;
    }

    public function setJobName($name)
    {
        $this->job_name = $name;
        return $this;
    }

    public function getJobArgs()
    {
        return $this->job_args;
    }

    public function setJobArgs($args)
    {
        $this->job_args = $args;
        return $this;
    }
}
