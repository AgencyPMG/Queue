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

namespace PMG\Queue\Test;

use PMG\Queue\Adapter\PheanstalkAdapter;

class PheanstalkAdapterTest extends \PHPUnit_Framework_TestCase
{
    public function testSetAndGetConnection()
    {
        $conn = $this->getMock('Pheanstalk_PheanstalkInterface');

        $adapt = new PheanstalkAdapter($conn);

        $this->assertSame($conn, $adapt->getConnection());

        $conn2 = $this->getMock('Pheanstalk_PheanstalkInterface');

        $adapt->setConnection($conn2);

        $this->assertSame($conn2, $adapt->getConnection());
    }

    public function testSetGetTube()
    {
        $conn = $this->getMock('Pheanstalk_PheanstalkInterface');

        $adapt = new PheanstalkAdapter($conn);

        $tube = 'a_tube';

        $adapt->setTube($tube);

        $this->assertEquals($adapt->getTube(), $tube);
    }

    /**
     * @expectedException PMG\Queue\Adapter\Exception\MustQuitException
     */
    public function testAcquireThrowsMustQuitException()
    {
        $conn = $this->getExceptionThrowingConn('watch', new \Pheanstalk_Exception_ClientException());

        $adapt = new PheanstalkAdapter($conn);

        $adapt->acquire();
    }

    /**
     * @expectedException PMG\Queue\Adapter\Exception\ClientException
     */
    public function testAcquireThrowsClientException()
    {
        $conn = $this->getExceptionThrowingConn('watch', new \Pheanstalk_Exception());

        $adapt = new PheanstalkAdapter($conn);

        $adapt->acquire();
    }

    /**
     * @expectedException PMG\Queue\Adapter\Exception\TimeoutException
     */
    public function testNoJobThrowsTimeoutException()
    {
        $conn = $this->getMock('Pheanstalk_PheanstalkInterface');

        $conn->expects($this->once())
            ->method('watch')
            ->will($this->returnValue($conn));

        $conn->expects($this->once())
            ->method('ignore')
            ->will($this->returnValue($conn));

        $conn->expects($this->once())
            ->method('reserve')
            ->will($this->returnValue(false));

        $adapt = new PheanstalkAdapter($conn);

        $adapt->acquire();
    }

    /**
     * @expectedException PMG\Queue\Adapter\Exception\BadJobBodyException
     */
    public function testBadJsonThrowsException()
    {
        $conn = $this->getMock('Pheanstalk_PheanstalkInterface');

        $conn->expects($this->once())
            ->method('watch')
            ->will($this->returnValue($conn));

        $conn->expects($this->once())
            ->method('ignore')
            ->will($this->returnValue($conn));

        $conn->expects($this->once())
            ->method('reserve')
            ->will($this->returnValue($this->getJob(123, 'this{bad json')));

        $adapt = new PheanstalkAdapter($conn);

        $adapt->acquire();
    }

    public function testAcquireReturnsArray()
    {
        $job_name = 'some_job';
        $body = array(
            PheanstalkAdapter::JOB_NAME => $job_name
        );

        $conn = $this->getMock('Pheanstalk_PheanstalkInterface');

        $conn->expects($this->once())
            ->method('watch')
            ->will($this->returnValue($conn));

        $conn->expects($this->once())
            ->method('ignore')
            ->will($this->returnValue($conn));

        $conn->expects($this->once())
            ->method('reserve')
            ->will($this->returnValue($this->getJob(123, json_encode($body))));

        $adapt = new PheanstalkAdapter($conn);

        $result = $adapt->acquire();

        $this->assertTrue(is_array($result));

        $this->assertEquals($result[0], $job_name);
    }

    private function getExceptionThrowingConn($method, \Exception $e)
    {
        $conn = $this->getMock('Pheanstalk_PheanstalkInterface');

        $conn->expects($this->once())
            ->method($method)
            ->will($this->throwException($e));

        return $conn;
    }

    private function getJob($id, $body)
    {
        // xxx probably should use a mock here.
        return new \Pheanstalk_Job($id, $body);
    }
}
