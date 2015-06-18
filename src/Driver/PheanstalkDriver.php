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

namespace PMG\Queue\Driver;

use Pheanstalk\Job;
use Pheanstalk\PheanstalkInterface;
use PMG\Queue\DefaultEnvelope;
use PMG\Queue\Envelope;
use PMG\Queue\Message;
use PMG\Queue\Exception\InvalidEnvelope;
use PMG\Queue\Serializer\Serializer;
use PMG\Queue\Driver\Pheanstalk\PheanstalkEnvelope;
use PMG\Queue\Driver\Pheanstalk\PheanstalkError;

/**
 * A driver implementatio backed by Pheanstalk & Beanstalkd.
 *
 * The options array takes a set of values related to how the messages are
 * put into beanstalkd.
 *
 * @since   2.0
 */
final class PheanstalkDriver extends AbstractPersistanceDriver
{
    /**
     * @var PheanstalkInterface
     */
    private $conn;

    /**
     * @var array
     */
    private $options;

    public function __construct(PheanstalkInterface $conn, array $options=null, Serializer $serializer=null)
    {
        parent::__construct($serializer);
        $this->conn = $conn;
        $this->options = array_replace([
            'priority'          => PheanstalkInterface::DEFAULT_PRIORITY,
            'delay'             => PheanstalkInterface::DEFAULT_DELAY,
            'ttr'               => PheanstalkInterface::DEFAULT_TTR,
            'retry-priority'    => PheanstalkInterface::DEFAULT_PRIORITY,
            'retry-delay'       => PheanstalkInterface::DEFAULT_DELAY,
            'retry-ttr'         => PheanstalkInterface::DEFAULT_TTR,
            'fail-priority'     => PheanstalkInterface::DEFAULT_PRIORITY,
            'reserve-timeout'   => 10,
        ], $options ?: []);
    }

    /**
     * {@inheritdoc}
     */
    public function enqueue($queueName, Message $message)
    {
        $env = new DefaultEnvelope($message);
        $data = $this->serialize($env);

        try {
            $id = $this->conn->putInTube(
                $queueName,
                $data,
                $this->options['priority'],
                $this->options['delay'],
                $this->options['ttr']
            );
        } catch (\Pheanstalk\Exception $e) {
            throw PheanstalkError::fromException($e);
        }

        return new PheanstalkEnvelope(new Job($id, $data), $env);
    }

    /**
     * {@inheritdoc}
     */
    public function dequeue($queueName)
    {
        $job = null;
        try {
            $job = $this->conn->reserveFromTube($queueName, $this->options['reserve-timeout']);
        } catch (\Pheanstalk\Exception $e) {
            throw PheanstalkError::fromException($e);
        }

        return $job ? new PheanstalkEnvelope($job, $this->unserialize($job->getData())) : null;
    }

    /**
     * {@inheritdoc}
     */
    public function ack($queueName, Envelope $env)
    {
        if (!$env instanceof PheanstalkEnvelope) {
            throw new InvalidEnvelope(sprintf(
                '%s requires that envelopes be instances of "%s", got "%s"',
                __CLASS__,
                PheanstalkEnvelope::class,
                get_class($env)
            ));
        }

        try {
            $this->conn->delete($env->getJob());
        } catch (\Pheanstalk\Exception $e) {
            throw PheanstalkError::fromException($e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function retry($queueName, Envelope $env)
    {
        if (!$env instanceof PheanstalkEnvelope) {
            throw new InvalidEnvelope(sprintf(
                '%s requires that envelopes be instances of "%s", got "%s"',
                __CLASS__,
                PheanstalkEnvelope::class,
                get_class($env)
            ));
        }

        $e = $env->retry();
        $data = $this->serialize($e);

        // since we need to update the job payload here, we have to delete
        // it and re-add it manually. This isn't transational, so there's
        // a (very real) possiblity of data loss.
        try {
            $this->conn->delete($env->getJob());
            $id = $this->conn->putInTube(
                $queueName,
                $data,
                $this->options['retry-priority'],
                $this->options['retry-delay'],
                $this->options['retry-ttr']
            );
        } catch (\Pheanstalk\Exception $e) {
            throw PheanstalkError::fromException($e);
        }

        return new PheanstalkEnvelope(new Job($id, $data), $e);
    }

    /**
     * {@inheritdoc}
     */
    public function fail($queueName, Envelope $env)
    {
        if (!$env instanceof PheanstalkEnvelope) {
            throw new InvalidEnvelope(sprintf(
                '%s requires that envelopes be instances of "%s", got "%s"',
                __CLASS__,
                PheanstalkEnvelope::class,
                get_class($env)
            ));
        }

        try {
            $this->conn->bury($env->getJob(), $this->options['fail-priority']);
        } catch (\Pheanstalk\Exception $e) {
            throw PheanstalkError::fromException($e);
        }
    }
}
