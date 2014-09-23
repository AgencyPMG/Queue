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

class PheanstalkAdapterTest extends \PHPUnit_Framework_TestCase
{
    private $conn, $adapt;

    public function testSetAndGetConnection()
    {
        $conn = $this->getMock('Pheanstalk_PheanstalkInterface');

        $this->assertSame($this->adapt, $this->adapt->setConnection($conn));
        $this->assertSame($conn, $this->adapt->getConnection());
    }

    public function testSetGetTube()
    {
        $tube = 'a_tube';
        $this->assertSame($this->adapt, $this->adapt->setTube($tube));
        $this->assertEquals($tube, $this->adapt->getTube());
    }

    /**
     * @expectedException PMG\Queue\Adapter\Exception\MustQuitException
     */
    public function testAcquireThrowsMustQuitException()
    {
        $conn = $this->getExceptionThrowingConn('watch', new \Pheanstalk_Exception_ClientException());
        $this->adapt->setConnection($conn);
        $this->adapt->acquire();
    }

    /**
     * @expectedException PMG\Queue\Adapter\Exception\ClientException
     */
    public function testAcquireThrowsClientException()
    {
        $conn = $this->getExceptionThrowingConn('watch', new \Pheanstalk_Exception());
        $this->adapt->setConnection($conn);
        $this->adapt->acquire();
    }

    /**
     * @expectedException PMG\Queue\Adapter\Exception\TimeoutException
     */
    public function testNoJobThrowsTimeoutException()
    {
        $this->conn->expects($this->once())
            ->method('watch')
            ->will($this->returnValue($this->conn));

        $this->conn->expects($this->once())
            ->method('ignore')
            ->will($this->returnValue($this->conn));

        $this->conn->expects($this->once())
            ->method('reserve')
            ->will($this->returnValue(false));

        $this->adapt->acquire();
    }

    /**
     * @expectedException PMG\Queue\Adapter\Exception\BadJobBodyException
     */
    public function testBadJsonThrowsException()
    {
        $this->conn->expects($this->once())
            ->method('watch')
            ->will($this->returnValue($this->conn));

        $this->conn->expects($this->once())
            ->method('ignore')
            ->will($this->returnValue($this->conn));

        $this->conn->expects($this->once())
            ->method('reserve')
            ->will($this->returnValue($this->getJob(123, 'this{bad json')));

        $this->adapt->acquire();
    }

    public function testAcquireReturnsArray()
    {
        $job_name = 'some_job';
        $body = array(
            PheanstalkAdapter::JOB_NAME => $job_name
        );

        $this->conn->expects($this->once())
            ->method('watch')
            ->will($this->returnValue($this->conn));

        $this->conn->expects($this->once())
            ->method('ignore')
            ->will($this->returnValue($this->conn));

        $this->conn->expects($this->once())
            ->method('reserve')
            ->will($this->returnValue($this->getJob(123, json_encode($body))));

        $result = $this->adapt->acquire();

        $this->assertTrue(is_array($result));
        $this->assertEquals($result[0], $job_name);
    }

    /**
     * @expectedException PMG\Queue\Adapter\Exception\NoActiveJobException
     */
    public function testFinishWithoutJob()
    {
        $this->adapt->finish();
    }

    /**
     * @depends testAcquireReturnsArray
     * @expectedException PMG\Queue\Adapter\Exception\MustQuitException
     */
    public function testFinishWithClientException()
    {
        $this->setUpAcquireForFinish();

        $this->conn->expects($this->once())
            ->method('delete')
            ->will($this->throwException(new \Pheanstalk_Exception_ClientException()));

        $this->adapt->acquire();
        $this->adapt->finish();
    }

    /**
     * @depends testAcquireReturnsArray
     * @expectedException PMG\Queue\Adapter\Exception\ClientException
     */
    public function testFinishWithException()
    {
        $this->setUpAcquireForFinish();

        $this->conn->expects($this->once())
            ->method('delete')
            ->will($this->throwException(new \Pheanstalk_Exception()));

        $this->adapt->acquire();
        $this->adapt->finish();
    }

    /**
     * @depends testAcquireReturnsArray
     */
    public function testSuccessfulFinish()
    {
        $this->setUpAcquireForFinish();

        $this->conn->expects($this->once())
            ->method('delete');

        $this->adapt->acquire();
        $this->adapt->finish();
    }

    /**
     * @expectedException PMG\Queue\Adapter\Exception\ClientException
     */
    public function testPutWithThrowingConnection()
    {
        $this->conn->expects($this->once())
            ->method('useTube')
            ->will($this->returnValue($this->conn));
        $this->conn->expects($this->once())
            ->method('put')
            ->will($this->throwException(new \Pheanstalk_Exception()));

        $this->adapt->put('a_name', array(), 1, 2);
    }

    public function testSuccessfulPut()
    {
        $body = array(
            'one'   => 'two',
        );

        $this->conn->expects($this->once())
            ->method('useTube')
            ->will($this->returnValue($this->conn));
        $this->conn->expects($this->once())
            ->method('put')
            ->with(
                $this->isType('string'),
                $this->isType('int'),
                10,
                1000
            );

        $this->assertTrue($this->adapt->put('a_name', $body, 1000, 10));
    }

    protected function setUp()
    {
        $this->conn = $this->getMock('Pheanstalk_PheanstalkInterface');
        $this->adapt = new PheanstalkAdapter($this->conn);
    }

    private function setUpAcquireForFinish()
    {
        $job_name = 'some_job';
        $body = array(
            PheanstalkAdapter::JOB_NAME => $job_name
        );

        $this->conn->expects($this->once())
            ->method('watch')
            ->will($this->returnValue($this->conn));

        $this->conn->expects($this->once())
            ->method('ignore')
            ->will($this->returnValue($this->conn));

        $this->conn->expects($this->once())
            ->method('reserve')
            ->will($this->returnValue($this->getJob(123, json_encode($body))));
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
        return new \Pheanstalk_Job($id, $body);
    }
}
