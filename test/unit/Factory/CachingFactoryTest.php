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

namespace PMG\Queue\Factory;

use PMG\Queue\QueueFactory;
use PMG\Queue\Queue\MemoryQueue;

class CachingFactoryTest extends \PMG\Queue\UnitTestCase
{
    private $wrapped, $factory;

    public function testForNameCallsUnderlingFactoryToCreateQueueOnce()
    {
        $queue = new MemoryQueue();
        $this->wrapped->expects($this->once())
            ->method('forName')
            ->with('test')
            ->willReturn($queue);


        $q = $this->factory->forName('test');

        $this->assertSame($queue, $q);

        // shouldn't call the wrapped factory again.
        $this->assertSame($queue, $this->factory->forName('test'));
    }

    protected function setUp()
    {
        $this->wrapped = $this->getMock(QueueFactory::class);
        $this->factory = new CachingFactory($this->wrapped);
    }
}
