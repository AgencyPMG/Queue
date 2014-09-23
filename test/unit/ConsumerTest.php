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

class ConsumerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \PMG\Queue\Exception\ConsumerException
     */
    public function testMustQuitExceptionThrowsConsumer()
    {
        $a = $this->getAdapterMock();

        $a->expects($this->once())
            ->method('acquire')
            ->will($this->throwException(new Adapter\Exception\MustQuitException()));

        $consumer = new Consumer($a);

        $consumer->runOnce();
    }

    public function testQueueExceptionReturnsOne()
    {
        $a = $this->getAdapterMock();

        $a->expects($this->once())
            ->method('acquire')
            ->will($this->throwException(new Adapter\Exception\TimeoutException()));

        $consumer = new Consumer($a);

        $this->assertEquals(1, $consumer->runOnce());
    }

    public function testUnkownExceptionReturnsOne()
    {
        $a = $this->getAdapterMock();

        $a->expects($this->once())
            ->method('acquire')
            ->will($this->throwException(new \RuntimeException("Something went wrong")));

        $consumer = new Consumer($a);

        $this->assertEquals(1, $consumer->runOnce());
    }

    public function testUnregisteredJobNameReturnsOne()
    {
        $a = $this->getAdapterMock();

        $a->expects($this->once())
            ->method('acquire')
            ->will($this->returnValue(array('some_job', array())));

        $consumer = new Consumer($a);

        $this->assertEquals(1, $consumer->runOnce());
    }

    public function testFailedJobReturnsCode()
    {
        $a = $this->getAdapterMock();

        $a->expects($this->once())
            ->method('acquire')
            ->will($this->returnValue(array('some_job', array())));

        $consumer = new Consumer($a, null, null, $this->getProcessMock());

        $consumer->whitelistJob('some_job', __NAMESPACE__ . '\\StubJob');

        $this->assertEquals($consumer->runOnce(), StubJob::CODE);
    }

    public function testSuccessfulJobReturnsZero()
    {
        $a = $this->getAdapterMock();

        $a->expects($this->once())
            ->method('acquire')
            ->will($this->returnValue(array('some_job', array())));

        $consumer = new Consumer($a, null, null, $this->getProcessMock());

        $consumer->whitelistJob('some_job', __NAMESPACE__ . '\\StubSuccess');

        $this->assertEquals($consumer->runOnce(), 0);
    }

    public function testPnctlErrorReturnsCode()
    {
        $a = $this->getAdapterMock();

        $a->expects($this->once())
            ->method('acquire')
            ->will($this->returnValue(array('some_job', array())));

        $consumer = new Consumer($a);

        $consumer->whitelistJob('some_job', __NAMESPACE__ . '\\StubJob');

        $this->assertEquals($consumer->runOnce(), StubJob::CODE);
    }

    public function testPnctlSuccessReturnZero()
    {
        $a = $this->getAdapterMock();

        $a->expects($this->once())
            ->method('acquire')
            ->will($this->returnValue(array('some_job', array())));

        $consumer = new Consumer($a);

        $consumer->whitelistJob('some_job', __NAMESPACE__ . '\\StubSuccess');

        $this->assertEquals($consumer->runOnce(), 0);
    }

    private function getAdapterMock()
    {
        return $this->getMock('PMG\\Queue\\Adapter\\AdapterInterface');
    }

    private function getProcessMock()
    {
        $stub = $this->getMock('PMG\\Queue\\ProcessManagerInterface');

        $stub->expects($this->any())
            ->method('fork')
            ->will($this->returnValue(-1));

        return $stub;
    }

    private function getJobMock()
    {
        return $this->getMock('PMG\\Queue\\JobInterface');
    }
}

class StubJob implements JobInterface
{
    const CODE = 101;

    public function work(array $args=array())
    {
        throw new \Exception("Broken", static::CODE);
    }
}

class StubSuccess implements JobInterface
{
    public function work(array $args=array())
    {
        return true;
    }
}

