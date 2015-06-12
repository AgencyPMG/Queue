<?php
/**
 * This file is part of PMG\Queue
 *
 * Copyright (c) PMG <https://www.pmg.com>
 *
 * For full copyright information see the LICENSE file distributed
 * with this source code.
 *
 * @license     http://opensource.org/licenses/MIT MIT
 */

namespace PMG\Queue\Driver\Pheanstalk;

use Pheanstalk\Job;
use PMG\Queue\Envelope;
use PMG\Queue\Message;

/**
 * The envelope that backs the Pheanstalk driver. This provides an additional
 * property to keep track of the job ID. This wraps another Envelope object
 * that comes back from the queue serialized.
 *
 * @since   2.0
 */
final class PheanstalkEnvelope implements Envelope
{
    /**
     * @var Job
     */
    private $job;

    /**
     * @var Envelope
     */
    private $wrapped;

    public function __construct(Job $job, Envelope $wrapped)
    {
        $this->job = $job;
        $this->wrapped = $wrapped;
    }

    /**
     * {@inheritdoc}
     */
    public function unwrap()
    {
        return $this->wrapped->unwrap();
    }

    /**
     * {@inheritdoc}
     */
    public function attempts()
    {
        return $this->wrapped->attempts();
    }

    /**
     * {@inheritdoc}
     */
    public function retry()
    {
        $new = clone $this;
        $new->wrapped = $this->wrapped->retry();

        return $new;
    }

    public function getJob()
    {
        return $this->job;
    }

    public function getJobId()
    {
        return $this->getJob()->getId();
    }
}
