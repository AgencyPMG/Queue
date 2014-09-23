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

class DummyAdapterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \PMG\Queue\Adapter\Exception\TimeoutException
     */
    public function testEmptyQueueThrowsTimeout()
    {
        $a = new DummyAdapter();
        $a->acquire();
    }

    public function testPutReturnsTrue()
    {
        $a = new DummyAdapter();
        $this->assertTrue($a->put('some_job', array('job_body' => 'yep')));
    }

    /**
     * @depends testPutReturnsTrue
     */
    public function testAcquireReturnsArray()
    {
        $a = new DummyAdapter();

        $body = array('job_body' => 'yep');
        $job_name = 'some_job';

        $a->put($job_name, $body);

        $result = $a->acquire();

        $this->assertTrue(is_array($result));
        $this->assertEquals(count($result), 2);

        $this->assertEquals($result[0], $job_name);

        $this->assertTrue(isset($result[1]['job_body']));
        $this->assertEquals($result[1]['job_body'], $body['job_body']);
    }

    /**
     * @expectedException \PMG\Queue\Adapter\Exception\NoActiveJobException
     */
    public function testPuntThrowsWithNoJob()
    {
        $a = new DummyAdapter();
        $a->punt();
    }

    /**
     * @expectedException \PMG\Queue\Adapter\Exception\NoActiveJobException
     */
    public function testFinishThrowsWithNoJob()
    {
        $a = new DummyAdapter();
        $a->finish();
    }

    public function testPuntReturnsTrue()
    {
        $a = new DummyAdapter();

        $a->put('some_job', array());

        $result = $a->acquire();

        $this->assertTrue($a->punt());
    }

    public function testFinishReturnsTrue()
    {
        $a = new DummyAdapter();

        $a->put('some_job', array());

        $result = $a->acquire();

        $this->assertTrue($a->finish());
    }
}
