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
class DummyAdapater implements AdapaterInterface
{
    /**
     * The current job.
     *
     * @since   0.1
     * @access  private
     * @var     string
     */
    private $current;

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

    }

    /**
     * From AdapaterInterface
     *
     * {@inheritdoc}
     */
    public function finish()
    {

    }

    /**
     * From AdapaterInterface
     *
     * {@inheritdoc}
     */
    public function punt()
    {

    }

    /**
     * From AdapaterInterface
     *
     * {@inheritdoc}
     */
    public function touch()
    {

    }

    /**
     * From AdapaterInterface
     *
     * {@inheritdoc}
     */
    public function put($ttr, array $job_body)
    {

    }
}
