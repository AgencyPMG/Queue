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

use Psr\Log\LogLevel;

class Consumer implements ConsumerInterface, AdapterAwareInterface, \Psr\Log\LoggerAwareInterface
{
    const E_STARTED         = 'started';
    const E_EXIT_FAIL       = 'exit_failure';
    const E_EXIT            = 'exit';
    const E_CHILD_EXIT      = 'child_exit';
    const E_CHILD_EXIT_FAIL = 'child_exit_failure';
    const E_CREATED_JOB     = 'job_class_instantiated';
    const E_QUEUE_EXCEPTION = 'caught_queue_exception';
    const E_EXCEPTION       = 'caught_exception';
    const E_NOJOB           = 'job_not_whitelisted';
    const E_PREFORK         = 'prefork';
    const E_POSTFORK        = 'postform';
    const E_JOB_FAILED      = 'job_failed';
    const E_JOB_FINISHED    = 'job_finished';

    /**
     * Container for the Adapater (server backend)
     *
     * @since   0.1
     * @access  private
     * @var     PMG\Queue\Adapater\AdapaterInterface
     */
    private $adapater;

    /**
     * Container for the event manager
     *
     * @since   0.1
     * @access  public
     * @var     Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    private $event;

    /**
     * Logger.
     *
     * @since   0.1
     * @access  public
     * @var     Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * Process manager.
     *
     * @since   0.1
     * @access  private
     * @var     PMG\Queue\ProcessManagerInterface
     */
    private $pm;

    /**
     * Container for whitelisted jobs
     *
     * @since   0.1
     * @access  private
     * @var     array[]
     */
    private $jobs = array();

    /**
     * The current child process.
     *
     * @since   0.1
     * @access  private
     * @var     int
     */
    private $child = null;

    /**
     * Constructor. Set the adapater and event manager.
     *
     * @since   0.1
     * @access  public
     * @param   PMG\Queue\Adapater\AdapterInterface $adpt
     * @param   Symfony\Component\EventDispatcher\EventDispatcherInterface
     * @return  void
     */
    public function __construct(
        Adapter\AdapterInterface $adpt,
        \Psr\Log\LoggerInterface $logger=null,
        \Symfony\Component\EventDispatcher\EventDispatcherInterface $event=null,
        ProcessManagerInterface $pm=null)
    {
        if (!$event) {
            $event = new \Symfony\Component\EventDispatcher\EventDispatcher();
        }

        if (!$logger) {
            $logger = new DummyLogger();
        }

        if (!$pm) {
            $pm = new ProcessManager();
        }

        $this->setAdapter($adpt);
        $this->setEventManager($event);
        $this->setLogger($logger);
        $this->setProcessManager($pm);
    }

    /**
     * From ConsumerInterface
     *
     * {@inheritdoc}
     */
    public function whitelistJob($name, $job_class)
    {
        $this->jobs[$name] = $job_class;
    }

    /**
     * From ConsumerInterface
     *
     * {@inheritdoc}
     */
    public function blacklistJob($name)
    {
        if (isset($this->jobs[$name])) {
            unset($this->jobs[$name]);
            return true;
        }

        return false;
    }

    /**
     * From ConsumerInterface
     *
     * {@inheritdoc}
     */
    public function run()
    {
        $this->dispatch(static::E_STARTED, new \Symfony\Component\EventDispatcher\Event());

        while (true) {
            $this->runOnce();
        }
    }

    /**
     * Do a single job.
     *
     * @since   0.1
     * @access  public
     * @return  void
     */
    public function runOnce()
    {
        $adapter = $this->getAdapter();

        try {
            list($job_name, $args) = $adapter->acquire();
        } catch (Adapter\Exception\MustQuitException $e) {
            $exit_code = $e->getCode();

            $this->dispatch(static::E_EXIT_FAIL, new Event\QuitEvent($exit_code));

            $this->log(LogLevel::EMERGENCY, "Got Adapater\\Exception\\MustQuitException with status code {$exit_code}, exiting");

            if ($exit_code > 255) {
                $exit_code = 1;
            }

            throw new Exception\ConsumerException("Got a must quit exception.", $exit_code, $e);
        } catch (Exception\QueueException $e) {
            $this->dispatch(static::E_QUEUE_EXCEPTION, new Event\ExceptionEvent($e));
            $this->log(LogLevel::ERROR, "Caught QueueException, continuing");
            return 1;
        } catch (\Exception $e) {
            $this->dispatch(static::E_EXCEPTION, new Event\ExceptionEvent($e));
            $this->log(LogLevel::ERROR, "Caught unexpected exception, continuing");
            return 1;
        }

        if (!isset($this->jobs[$job_name])) {
            $this->dispatch(static::E_NOJOB, new Event\NoJobEvent($job_name, $args));
            $this->log(LogLevel::WARNING, "Got non-whitelisted job {$job_name}, continuing");
            return 1;
        }

        $code = $this->doJob($job_name, $args);

        $status_event = new Event\JobStatusEvent($job_name);

        $this->log(LogLevel::INFO, "Finished job {$job_name} with exit code {$code}");

        if (0 === $code) {
            try {
                $adapter->finish();
            } catch (Exception\QueueException $except) {
                $this->dispatch(static::E_QUEUE_EXCEPTION, new Event\ExceptionEvent($except));
            }

            $this->dispatch(static::E_JOB_FINISHED, $status_event);
        } else {
            try {
                $adapter->punt();
            } catch (Exception\QueueException $except) {
                $this->dispatch(static::E_QUEUE_EXCEPTION, new Event\ExceptionEvent($except));
            }

            $this->dispatch(static::E_JOB_FAILED, $status_event);
        }

        return $code;
    }

    /**
     * From AdapaterAwareInterface
     *
     * {@inheritdoc}
     */
    public function setAdapter(\PMG\Queue\Adapter\AdapterInterface $adpt)
    {
        $this->adapater = $adpt;
    }

    /**
     * From AdapaterAwareInterface
     *
     * {@inheritdoc}
     */
    public function getAdapter()
    {
        return $this->adapater;
    }

    /**
     * From Psr\Log\LoggerAwareInterface
     *
     * {@inheritdoc}
     */
    public function setLogger(\Psr\Log\LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Set the event dispatcher.
     *
     * @since   0.1
     * @access  public
     * @param   Symfony\Component\EventDispatcher\EventDispatcher $event
     * @return  $this
     */
    public function setEventManager(\Symfony\Component\EventDispatcher\EventDispatcherInterface $event)
    {
        $this->event = $event;
        return $this;
    }

    /**
     * Get the event dispatcher
     *
     * @since   0.1
     * @access  public
     * @return  Symfony\Component\EventDispatcher\EventDispatcherInterface $event
     */
    public function getEventManager()
    {
        return $this->event;
    }

    /**
     * Set the process manager.
     *
     * @since   0.1
     * @access  public
     * @param   PMG\Queue\ProcessManager
     * @return  $this
     */
    public function setProcessManager(ProcessManagerInterface $pm)
    {
        $this->pm = $pm;
        return $this;
    }

    /**
     * Get the process manager.
     *
     * @since   0.1
     * @access  public
     * @return  PMG\Queue\ProcessManagerInterface
     */
    public function getProcessManager()
    {
        return $this->pm;
    }

    /**
     * Get the current child process ID (or null)
     *
     * @since   0.1
     * @access  public
     * @return  int|null
     */
    public function getChild()
    {
        return $this->child;
    }

    /**
     * Dispatch an event.
     *
     * @since   0.1
     * @access  public
     * @param   string $name The event name
     * @param   Symfony\Component\EventDispatcher\Event $e
     * @return  void
     */
    protected function dispatch($name, \Symfony\Component\EventDispatcher\Event $e)
    {
        $this->getEventManager()->dispatch($name, $e);
    }

    /**
     * Log something.
     *
     * @since   0.1
     * @access  public
     * @param   string $level
     * @param   string $message
     * @param   array $context
     * @return  void
     */
    protected function log($level, $message, array $context=array())
    {
        $this->logger->log($level, $message, $context);
    }

    protected function doJob($job_name, $args)
    {
        $job = $this->createJobInstance($this->jobs[$job_name]);

        $e = new Event\JobEvent($job);

        $this->dispatch(static::E_PREFORK, $e);

        $pm = $this->getProcessManager();

        $this->child = $child = $pm->fork();

        $status = 0;

        if ($child <= 0) {
            // child thread
            try {
                $job->work($args);
            } catch (\Exception $except) {
                $status = $except->getCode();
                if ($status > 255) {
                    $status = 1;
                }

                // if we forked, child will be zero
                if (0 === $child) {
                    exit($status);
                }

                // if we're here we didn't fork, return the status to the
                // runOnce caller to deal with
                return $status;
            }

            // if we forked, child will be zero
            if (0 === $child) {
                exit(0);
            }
        } elseif ($child > 0) {
            // parent thread
            $this->dispatch(static::E_POSTFORK, $e);

            $status = $pm->wait($child);

            $this->child = null;

            return $pm->getStatus($status);
        }

        // if we're here for some reason, we didn't actually fork
        // pretend we did and return 0, success, we would've caught
        // any error earlier
        return 0;
    }

    protected function createJobInstance($class)
    {
        $job = new $class;

        $this->dispatch(static::E_CREATED_JOB, new Event\JobEvent($job));

        $this->log(LogLevel::DEBUG, "Created job class {$class}");

        return $job;
    }
}
