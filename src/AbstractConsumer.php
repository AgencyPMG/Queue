<?php
/**
 * This file is part of PMG\Queue
 *
 * Copyright (c) PMG <https://www.pmg.com>
 *
 * For full copyright information see the LICENSE file distributed
 * with this source code.
 *
 * @license     http://opensource.org/licenses/Apache-2.0 Apache-2.0
 */

namespace PMG\Queue;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * ABC for consumers, provides `run` and `stop` along with their default
 * implementations to make it easier to decorate consumers to add extra stuff.
 *
 * @since 3.0
 */
abstract class AbstractConsumer implements Consumer
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var boolean
     */
    private $running = false;

    /**
     * @var int
     */
    private $exitCode = self::EXIT_SUCCESS;

    public function __construct(LoggerInterface $logger=null)
    {
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function run($queueName)
    {
        $this->running = true;
        while ($this->running) {
            try {
                $this->once($queueName);
            } catch (Exception\MustStop $e) {
                $this->getLogger()->warning('Caught a must stop exception, exiting: {msg}', [
                    'msg'   => $e->getMessage(),
                ]);
                $this->stop($e->getCode());
            } catch (\Exception $e)  {
                // likely means means something went wrong with the driver
                $this->logFatalAndStop($e);
            } catch (\Throwable $e) {
                // an `Error` exception, etc
                $this->logFatalAndStop($e);
            }
        }

        return $this->exitCode;
    }

    /**
     * {@inheritdoc}
     */
    public function stop($code=null)
    {
        $this->running = false;
        $this->exitCode = null === $code ? self::EXIT_SUCCESS : intval($code);
    }

    protected function getLogger()
    {
        if (!$this->logger) {
            $this->logger = new NullLogger();
        }

        return $this->logger;
    }

    protected function logFatalAndStop($exception)
    {
        $this->getLogger()->emergency('Caught an unexpected {cls} exception, exiting: {msg}', [
            'cls' => get_class($exception),
            'msg' => $exception->getMessage(),
        ]);
        $this->stop(self::EXIT_ERROR);
    }
}
