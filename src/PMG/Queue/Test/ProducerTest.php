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

use PMG\Queue\Producer;

class ProducerTest extends \PHPUnit_Framework_TestCase
{
    public function testSetGetAdapter()
    {
        $adapter = $this->getMock('PMG\\Queue\\Adapter\\AdapterInterface');

        $producer = new Producer($adapter);

        $this->assertSame($producer->getAdapter(), $adapter);

        $new_adapt = $this->getMock('PMG\\Queue\\Adapter\\AdapterInterface');

        $producer->setAdapter($new_adapt);

        $this->assertSame($producer->getAdapter(), $new_adapt);
    }

    public function testSuccessfulPutReturnsTrue()
    {
        $adapter = $this->getMock('PMG\\Queue\\Adapter\\AdapterInterface');

        $adapter->expects($this->once())
            ->method('put')
            ->with('say_hello', array('name' => 'Chris'))
            ->will($this->returnValue(true));

        $producer = new Producer($adapter);

        $this->assertTrue($producer->addJob('say_hello', array('name' => 'Chris')));
    }
}
