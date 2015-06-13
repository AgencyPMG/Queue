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

namespace PMG\Queue;

class DefaultProducerTest extends UnitTestCase
{
    private $router, $driver, $producer;

    public function testProducerRoutesMessageAndPutsItIntoAQueue()
    {
        $msg = $this->getMock(Message::class);
        $this->router->expects($this->once())
            ->method('queueFor')
            ->with($this->identicalTo($msg))
            ->willReturn('testq');
        $this->driver->expects($this->once())
            ->method('enqueue')
            ->with('testq', $this->identicalTo($msg));

        $this->producer->send($msg);
    }

    /**
     * @expectedException PMG\Queue\Exception\QueueNotFound
     */
    public function testProducerErrorsWhenNoQueueIsFound()
    {
        $msg = $this->getMock(Message::class);
        $this->router->expects($this->once())
            ->method('queueFor')
            ->with($this->identicalTo($msg))
            ->willReturn(null);
        $this->driver->expects($this->never())
            ->method('enqueue');

        $this->producer->send($msg);
    }

    protected function setUp()
    {
        $this->router = $this->getMock(Router::class);
        $this->driver = $this->getMock(Driver::class);
        $this->producer = new DefaultProducer($this->driver, $this->router);
    }
}
