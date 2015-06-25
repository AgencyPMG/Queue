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

use PMG\Queue\SimpleMessage;
use PMG\Queue\Driver\Pheanstalk\PheanstalkEnvelope;

/**
 * Tests all the "happy" paths of the pheanstalk driver: no exceptions
 */
class HappyPheanstalkDriverTest extends \PMG\Queue\IntegrationTestCase
{
    private $conn, $driver, $seenTubes = [];

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

    public function testBroadcastedJobsAreSentToAllTubes()
    {
        $this->driver->broadcast(new SimpleMessage('broadcast'));

        // this is the only time we use `default`, should be relatively safe
        $env = $this->driver->dequeue('default');

        $this->assertEquals('broadcast', $env->unwrap()->getName());
    }

    protected function setUp()
    {
        $host = getenv('PMG_QUEUE_HOST') ?: 'localhost';
        $port = intval(getenv('PMG_QUEUE_PORT') ?: \Pheanstalk\PheanstalkInterface::DEFAULT_PORT);
        $this->conn = new \Pheanstalk\Pheanstalk($host, $port);
        $this->driver = new PheanstalkDriver($this->conn, [
            'reserve-timeout'   => 1,
        ]);

        try {
            $this->seenTubes = array_fill_keys($this->conn->listTubes(), true);
        } catch (\Pheanstalk\Exception\ConnectionException $e) {
            $this->markTestSkipped(sprintf('Beanstalkd server is not running on %s:%d', $host, $port));
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
