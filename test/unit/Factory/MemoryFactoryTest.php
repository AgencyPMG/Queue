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

use PMG\Queue\Queue\MemoryQueue;

class MemoryFactoryTest extends \PMG\Queue\UnitTestCase
{
    public function testForNameCreatesNewMemoryQueueObjects()
    {
        $fac = new MemoryFactory();

        $one = $fac->forName('TestQueue');
        $this->assertInstanceOf(MemoryQueue::class, $one);

        $this->assertNotSame($one, $fac->forName('TestQueue'));
    }
}
