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

namespace PMG\Queue\Driver;

use PMG\Queue\SimpleMessage;
use PMG\Queue\DefaultEnvelope;
use PMG\Queue\Driver\Pheanstalk\PheanstalkEnvelope;

class PheanstalkDriverTest extends \PMG\Queue\IntegrationTestCase
{
    private $conn, $driver, $seenTubes = [];

    /**
     * @expectedException PMG\Queue\Exception\InvalidEnvelope
     */
    public function testAckCannotBeCalledWithABadEnvelope()
    {
        $this->driver->ack($this->randomTube(), new DefaultEnvelope(new SimpleMessage('t')));
    }

    /**
     * @expectedException PMG\Queue\Exception\InvalidEnvelope
     */
    public function testRetryCannotBeCalledWithABadEnvelope()
    {
        $this->driver->retry($this->randomTube(), new DefaultEnvelope(new SimpleMessage('t')));
    }

    /**
     * @expectedException PMG\Queue\Exception\InvalidEnvelope
     */
    public function testFailCannotBeCalledWithABadEnvelope()
    {
        $this->driver->fail($this->randomTube(), new DefaultEnvelope(new SimpleMessage('t')));
    }

    public function testDequeueReturnsNullWhenNoJobsAreFound()
    {
        $this->assertNull($this->driver->dequeue($this->randomTube()));
    }

    /**
     * @expectedException Pheanstalk\Exception\ServerException
     * @expectedExceptionMessage NOT_FOUND
     */
    public function testJobsCanBeEnqueuedAndDequeuedAndRemovedWithAck()
    {
        $tube = $this->randomTube();

        $env = $this->driver->enqueue($tube, new SimpleMessage('TestMessage'));
        $this->assertEnvelope($env);

        $env2 = $this->driver->dequeue($tube);
        $this->assertEnvelope($env2);
        $this->assertEquals('TestMessage', $env2->unwrap()->getName());

        $this->assertEquals($env->getJobId(), $env2->getJobId());

        $this->driver->ack($tube, $env2);

        // this throws so we can check for NOT_FOUND
        $this->conn->statsJob($env2->getJob());
    }

    public function testJobsCanBeEnqueuedDequeuedAndRetriedWithRetry()
    {
        $tube = $this->randomTube();

        $env = $this->driver->enqueue($tube, new SimpleMessage('TestMessage'));
        $this->assertEnvelope($env);

        $env2 = $this->driver->dequeue($tube);
        $this->assertEnvelope($env2);

        $this->assertEquals($env->getJobId(), $env2->getJobId());

        $env3 = $this->driver->retry($tube, $env2);

        // just to make sure we put the job in
        $this->conn->statsJob($env3->getJob());
    }

    public function testJobsAreBuriedWithRetry()
    {
        $tube = $this->randomTube();

        $env = $this->driver->enqueue($tube, new SimpleMessage('TestMessage'));
        $this->assertEnvelope($env);

        $env2 = $this->driver->dequeue($tube);
        $this->assertEnvelope($env2);

        $this->assertEquals($env->getJobId(), $env2->getJobId());

        $this->driver->fail($tube, $env2);

        $res = $this->conn->statsJob($env2->getJob());
        $this->assertArrayHasKey('state', $res);
        $this->assertEquals('buried', $res['state']);
    }

    protected function setUp()
    {
        $this->conn = new \Pheanstalk\Pheanstalk(
            getenv('PMG_QUEUE_HOST') ?: 'localhost',
            getenv('PMG_QUEUE_PORT') ?: \Pheanstalk\PheanstalkInterface::DEFAULT_PORT
        );
        $this->driver = new PheanstalkDriver($this->conn, [
            'reserve-timeout'   => 1,
        ]);

        try {
            $this->seenTubes = array_fill_keys($this->conn->listTubes(), true);
        } catch (\Pheanstalk\Exception\ConnectionException $e) {
            $this->markTestSkipped('Beanstalkd Server is Not Running');
        }
    }

    private function randomTube()
    {
        do {
            $tube = uniqid('tube_', true);
        } while (isset($this->seenTubes[$tube]));

        $this->seenTubes[$tube] = true;

        return $tube;
    }

    private function assertEnvelope($env)
    {
        $this->assertInstanceOf(PheanstalkEnvelope::class, $env);
    }
}
