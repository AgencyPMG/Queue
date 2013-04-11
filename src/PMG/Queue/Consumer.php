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

class Consumer implements ConsumerInterface, AdapaterAwareInterface, \Psr\Log\LoggerAwareInterface
{
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
     * Container for whitelisted jobs
     *
     * @since   0.1
     * @access  private
     * @var     array[]
     */
    private $jobs = array();

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
        Adapter\AdapaterInterface $adpt,
        \Psr\Log\LoggerInterface $logger,
        \Symfony\Component\EventDispatcher\EventDispatcherInterface $event=null)
    {
        if (!$event) {
            $event = new \Symfony\Component\EventDispatcher\EventDispatcher();
        }

        $this->setAdapater($adpt);
        $this->setLogger($logger);
        $this->setEventManager($event);
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
    public function run()
    {
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
        $adapater = $this->getAdapater();

        try {
            list($job_name, $args) = $adapater->acquire();
        } catch (Adapater\Exception\MustQuitException $e) {
            $exit_code = $e->getCode();

            $this->dispatch(static::E_EXIT_FAIL, new Event\QuitEvent($exit_code));

            $this->log(LogLevel::EMERGENCY, "Got Adapater\\Exception\\MustQuitException with status code {$exit_code}, exiting");

            exit($exit_code);
        } catch (Exception\QueueException $e) {
            $this->dispatch(static::E_QUEUE_EXCEPTION, new Event\ExceptionEvent($e));
            $this->log(LogLevel::ERROR, "Caught QueueException, continuing");
            continue;
        } catch (\Exception $e) {
            $this->dispatch(static::E_EXCEPTION, new Event\ExceptionEvent($e));
            $this->log(LogLevel::ERROR, "Caught unexpected exception, continuing");
            continue;
        }

        if (!isset($this->jobs[$job_name])) {
            $this->dispatch(static::E_NOJOB, new Event\NoJobEvent($job_name, $args));
            $this->log(LogLevel::WARNING, "Got non-whitelisted job {$job_name}, continuing");
            continue;
        }

        $code = $this->doJob($job_name, $args);

        if (0 === $code) {
            $adapater->finish();
            $this->dispatch(static::E_JOB_FINISHED, $e);
        } else {
            $adapater->punt();
            $this->dispatch(static::E_JOB_FAILED, $e);
        }
    }

    /**
     * From AdapaterAwareInterface
     *
     * {@inheritdoc}
     */
    public function setAdapter(\PMG\Queue\Adapater\AdapaterInterface $adpt)
    {
        $this->adapater = $adpt;
    }

    /**
     * From AdapaterAwareInterface
     *
     * {@inheritdoc}
     */
    public function getAdapater()
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
        $job = $this->createJobClass($this->jobs[$job_name]);

        $e = new EventJobEvent($job);

        $this->dispatch(static::E_PREFORK, $e);

        $child = static::fork();

        if (0 === $child || false === $child) {
            // child thread
            try {
                $job->work($args);
            } catch (\Exception $except) {
                $status = $e->getCode();
                if ($status > 255) {
                    $status = 1;
                }

                if (false === $child) {
                    return $status;
                }

                exit($status);
            }

            if (false === $child) {
                return 0;
            }

            exit(0);
        } elseif ($child > 0) {
            // parent thread
            $this->dispatch(static::E_POSTFORK, $e);

            pnctl_wait($status);

            return pnctl_wexitstatus($status);
        }

        // if we're here for some reason, we didn't actually fork
        // pretend we did and return 0
        return 0;
    }

    protected function createJobInstance($class)
    {
        $job = new $class;

        $this->dispatch(static::E_CREATED_JOB, new Event\JobEvent($job));

        $this->log(LogLevel::DEBUG, "Created job class {$class}");

        return $job;
    }

    protected static function fork()
    {
        if (!function_exists('pnctl_fork')) {
            return false; // fake like we're a child function
        }

        $pid = pnctl_fork();

        if (-1 === $pid) {
            return false; // xxx maybe this should fail here?
        }

        return $pid;
    }
}
