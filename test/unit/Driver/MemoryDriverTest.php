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

use PMG\Queue\Envelope;
use PMG\Queue\SimpleMessage;

class MemoryDriverTest extends \PMG\Queue\UnitTestCase
{
    const Q = 'TestQueue';

    private $driver;

    public function testEmptyQueueReturnsNullWhenDequeued()
    {
        $this->assertNull($this->driver->dequeue(self::Q));
    }

    public function testMessagesCanBeEnqueuedAndDequeued()
    {
        $m = new SimpleMessage('test');
        $this->assertInstanceOf(Envelope::class, $this->driver->enqueue(self::Q, $m));

        $e = $this->driver->dequeue(self::Q);
        $this->assertSame($m, $e->unwrap());

        $this->driver->retry(self::Q, $e);

        $e = $this->driver->dequeue(self::Q);
        $this->assertSame($m, $e->unwrap());
    }

    protected function setUp()
    {
        $this->driver = new MemoryDriver();
    }
}
