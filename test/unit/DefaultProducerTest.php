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
    private $router, $queue, $factory, $producer;

    public function testProducerRoutesMessageAndPutsItIntoAQueue()
    {
        $msg = $this->getMock(Message::class);
        $this->router->expects($this->once())
            ->method('queueFor')
            ->with($this->identicalTo($msg))
            ->willReturn('testq');
        $this->factory->expects($this->once())
            ->method('forName')
            ->with('testq')
            ->willReturn($this->queue);
        $this->queue->expects($this->once())
            ->method('enqueue')
            ->with($this->identicalTo($msg));

        $this->producer->send($msg);
    }

    protected function setUp()
    {
        $this->router = $this->getMock(Router::class);
        $this->queue = $this->getMock(Queue::class);
        $this->factory = $this->getMock(QueueFactory::class);
        $this->producer = new DefaultProducer($this->router, $this->factory);
    }
}
