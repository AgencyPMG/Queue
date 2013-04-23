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
 * A fake adapater.
 *
 * @since   0.1
 * @author  Christopher Davis <chris@pmg.co>
 */
class DummyAdapter implements AdapterInterface
{
    /**
     * The current job.
     *
     * @since   0.1
     * @access  private
     * @var     string
     */
    private $current = null;

    /**
     * The queue.
     *
     * @since   0.1
     * @access  public
     * @var     SplQueue
     */
    private $queue;

    /**
     * Constructor. Create the SplQueue
     *
     * @since   0.1
     * @access  public
     * @return  void
     */
    public function __construct()
    {
        $this->queue = new \SplQueue();
    }

    /**
     * From AdapaterInterface
     *
     * {@inheritdoc}
     */
    public function acquire()
    {
        try {
            $job = $this->queue->dequeue();
        } catch (\RuntimeException $e) {
            throw new Exception\TimeoutException("No job available", $e->getCode(), $e);
        }

        $this->current = $job;

        $job_name = isset($job[static::JOB_NAME]) ? $job[static::JOB_NAME] : false;

        return array($job_name, $job);
    }

    /**
     * From AdapaterInterface
     *
     * {@inheritdoc}
     */
    public function finish()
    {
        if ($this->current) {
            $this->current = null;
            return true;
        }

        $this->noJob();
    }

    /**
     * From AdapaterInterface
     *
     * {@inheritdoc}
     */
    public function punt()
    {
        if ($this->current) {
            $this->queue->enqueue($this->current);
            $this->current = null;
            return true;
        }

        $this->noJob();
    }

    /**
     * From AdapaterInterface
     *
     * {@inheritdoc}
     */
    public function touch()
    {
        // do nothing
    }

    /**
     * From AdapaterInterface
     *
     * {@inheritdoc}
     */
    public function put($job_name, array $job_body, $ttr=null)
    {
        $job_body[static::JOB_NAME] = $job_name;

        $this->queue->enqueue($job_body);

        return true;
    }

    private function noJob()
    {
        throw new Exception\NoActiveJobException("No currently active job");
    }
}
