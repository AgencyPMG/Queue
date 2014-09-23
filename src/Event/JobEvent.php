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
 * Fired when a job class is instaniated and before work gets done.
 *
 * @since   0.1
 * @author  Christopher Davis <chris@pmg.co>
 */
class JobEvent extends \Symfony\Component\EventDispatcher\Event
{
    private $job;

    public function __construct(\PMG\Queue\JobInterface $job)
    {
        $this->setJob($job);
    }

    public function getJob()
    {
        return $this->job;
    }

    public function setJob(\PMG\Queue\JobInterface $job)
    {
        $this->job = $job;
        return $this;
    }
}
