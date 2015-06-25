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

    public function testMessagesBroadcastedMessagesAreSentToAllQueues()
    {
        // add some queues...
        $m = new SimpleMessage('test');
        $this->assertInstanceOf(Envelope::class, $this->driver->enqueue(self::Q, $m));
        $this->assertInstanceOf(Envelope::class, $this->driver->enqueue(self::Q.'2', $m));

        $this->driver->broadcast(new SimpleMessage('broadcast'));

        $this->assertCount(2, $this->driver->getMessages(self::Q));
        $this->assertCount(2, $this->driver->getMessages(self::Q.'2'));
    }

    protected function setUp()
    {
        $this->driver = new MemoryDriver();
    }
}
