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
use PMG\Queue\SimpleMessage;
use PMG\Queue\DefaultEnvelope;
use PMG\Queue\Driver\Pheanstalk\PheanstalkEnvelope;

/**
 * Tests all the "unhappy" paths for the pheanstalk driver. This test
 * purposefully tries to cause errors by giving invalid hosts, etc.
 */
class UnhappyPheanstalkDriverTest extends \PMG\Queue\IntegrationTestCase
{
    private $conn, $driver;

    /**
     * @expectedException PMG\Queue\Exception\InvalidEnvelope
     */
    public function testAckCannotBeCalledWithABadEnvelope()
    {
        $this->driver->ack('q', new DefaultEnvelope(new SimpleMessage('t')));
    }

    /**
     * @expectedException PMG\Queue\Exception\InvalidEnvelope
     */
    public function testRetryCannotBeCalledWithABadEnvelope()
    {
        $this->driver->retry('q', new DefaultEnvelope(new SimpleMessage('t')));
    }

    /**
     * @expectedException PMG\Queue\Exception\InvalidEnvelope
     */
    public function testFailCannotBeCalledWithABadEnvelope()
    {
        $this->driver->fail('q', new DefaultEnvelope(new SimpleMessage('t')));
    }

    /**
     * @expectedException PMG\Queue\Driver\Pheanstalk\PheanstalkError
     */
    public function testEnqueueErorrsWhenTheUnderlyingConnectionErrors()
    {
        $this->driver->enqueue('q', new SimpleMessage('test'));
    }

    /**
     * @expectedException PMG\Queue\Driver\Pheanstalk\PheanstalkError
     */
    public function testDequeueErorrsWhenTheUnderlyingConnectionErrors()
    {
        $this->driver->dequeue('q', new SimpleMessage('test'));
    }

    /**
     * @expectedException PMG\Queue\Driver\Pheanstalk\PheanstalkError
     */
    public function testAckErrorsWhenUnderlyingConnectionErrors()
    {
        $this->driver->ack('q', $this->env);
    }

    /**
     * @expectedException PMG\Queue\Driver\Pheanstalk\PheanstalkError
     */
    public function testRetryErrorsWhenUnderlyingConnectionErrors()
    {
        $this->driver->retry('q', $this->env);
    }

    /**
     * @expectedException PMG\Queue\Driver\Pheanstalk\PheanstalkError
     */
    public function testFailErrorsWhenUnderlyingConnectionErrors()
    {
        $this->driver->fail('q', $this->env);
    }

    protected function setUp()
    {
        $this->conn = new \Pheanstalk\Pheanstalk('localhost', 65000);
        $this->driver = new PheanstalkDriver($this->conn);
        $this->env = new PheanstalkEnvelope(
            new Job(123, 't'),
            new DefaultEnvelope(new SimpleMessage('t'))
        );
    }
}
