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
 * Use Beanstalkd as a backend.
 *
 * @since   0.1
 * @author  Christopher Davis <chris@pmg.co>
 */
class PheanstalkAdapter implements AdapterInterface
{
    const TO = 5;

    /**
     * A Pheanstalk connection.
     *
     * @since   0.1
     * @access  private
     * @var     Pheanstalk_PheanstalkInterface
     */
    private $conn = null;

    /**
     * The current job ID.
     *
     * @since   0.1
     * @access  public
     * @var     Pheanstalk_Job
     */
    private $current = null;

    /**
     * The tube on which the adapter should listen for commands.
     *
     * @since   0.1
     * @access  private
     * @var     string
     */
    private  $tube = 'pmg_queue';

    /**
     * Constructor. Set a the Pheanstalk instance or create one.
     *
     * @since   0.1
     * @access  public
     * @return  void
     */
    public function __construct(\Pheanstalk_PheanstalkInterface $ph=null, $tube='pmg_queue')
    {
        if (!$ph) {
            $ph = new \Pheanstalk_Pheanstalk('127.0.0.1');
        }

        $this->setConnection($ph);
        $this->setTube($tube);
    }

    /** Getters/Setters **********/

    /**
     * Set the connection.
     *
     * @since   0.1
     * @access  public
     * @param   Pheanstalk_PheanstalkInterface $ph
     * @return  $this
     */
    public function setConnection(\Pheanstalk_PheanstalkInterface $ph)
    {
        $this->conn = $ph;
        return $this;
    }

    /**
     * Get the connection.
     *
     * @since   0.1
     * @access  public
     * @return  Pheanstalk_PheanstalkInterface
     */
    public function getConnection()
    {
        return $this->conn;
    }

    /**
     * Set the tube.
     *
     * @since   0.1
     * @access  public
     * @param   string $tube
     * @return  $this
     */
    public function setTube($tube)
    {
        $this->tube = $tube;
        return $this;
    }

    /**
     * Get the tube.
     *
     * @since   0.1
     * @access  public
     * @return  string
     */
    public function getTube()
    {
        return $this->tube;
    }

    /**
     * From AdapaterInterface
     *
     * {@inheritdoc}
     */
    public function acquire()
    {
        try {
            $job = $this->getConnection()
                ->watch($this->getTube())
                ->ignore('default')
                ->reserve(static::TO);
        } catch (\Pheanstalk_Exception_ClientException $e) {
            // error with the socket, have to quit.
            throw new Exception\MustQuitException("Pheanstalk client error", intval($e->getCode()), $e);
        } catch (\Pheanstalk_Exception $e) {
            throw new Exception\ClientException("Caught Pheanstalk_Exception", intval($e->getCode()), $e);
        }

        if (!$job) {
            throw new Exception\TimeoutException("No job acquired. Try again.");
        }

        $this->current = $job;

        $body = json_decode($job->getData(), true);

        if (!$body) {
            throw new Exception\BadJobBodyException("Could not json_decode job body");
        }

        return array(isset($body[static::JOB_NAME]) ? $body[static::JOB_NAME] : false, $body);
    }

    /**
     * From AdapaterInterface
     *
     * {@inheritdoc}
     */
    public function finish()
    {
        if (!$this->current) {
            throw new Exception\NoActiveJobException('No currently open jobs');
        }

        try {
            $this->getConnection()->delete($this->current);
        } catch (\Pheanstalk_Exception_ClientException $e) {
            // error with the socket, have to quit.
            throw new Exception\MustQuitException("Pheanstalk client error", intval($e->getCode()), $e);
        } catch (\Pheanstalk_Exception $e) {
            throw new Exception\ClientException("Caught Pheanstalk_Exception", intval($e->getCode()), $e);
        }

        $this->current = null;
    }

    /**
     * From AdapaterInterface
     *
     * {@inheritdoc}
     */
    public function punt($ttr=null)
    {
        if (!$this->current) {
            throw new Exception\NoActiveJobException('No currently open jobs');
        }

        $prio = \Pheanstalk_PheanstalkInterface::DEFAULT_PRIORITY - 1;

        try {
            $this->getConnection()->release($this->current, $prio);
        } catch (\Pheanstalk_Exception_ClientException $e) {
            // error with the socket, have to quit.
            throw new Exception\MustQuitException("Pheanstalk client error", intval($e->getCode()), $e);
        } catch (\Pheanstalk_Exception $e) {
            throw new Exception\ClientException("Caught Pheanstalk_Exception", intval($e->getCode()), $e);
        }

        $this->current = null;
    }

    /**
     * From AdapaterInterface
     *
     * {@inheritdoc}
     */
    public function touch()
    {
        if (!$this->current) {
            throw new Exception\NoActiveJobException('No currently open jobs');
        }

        try {
            $this->getConnection()->touch($this->current);
        } catch (\Pheanstalk_Exception_ClientException $e) {
            // error with the socket, have to quit.
            throw new Exception\MustQuitException("Pheanstalk client error", intval($e->getCode()), $e);
        } catch (\Pheanstalk_Exception $e) {
            throw new Exception\ClientException("Caught Pheanstalk_Exception", intval($e->getCode()), $e);
        }
    }

    /**
     * From AdapaterInterface
     *
     * {@inheritdoc}
     */
    public function put($job_name, array $job_body, $ttr=null)
    {
        if (!$ttr) {
            $ttr = \Pheanstalk_PheanstalkInterface::DEFAULT_TTR;
        }

        $job_body[static::JOB_NAME] = $job_name;

        try {
            $this->getConnection()
                ->useTube($this->getTube())
                ->put(json_encode($job_body), $ttr);
        } catch (\Pheanstalk_Exception $e) {
            throw new Exception\ClientException("Caught Pheanstalk_Exception", intval($e->getCode()), $e);
        }
    }
}
