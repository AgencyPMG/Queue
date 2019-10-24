<?php declare(strict_types=1);

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

use PMG\Queue\DefaultEnvelope;
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

    public function testEnvelopesCanBeEnqueuedAndDequeued()
    {
        $m = new DefaultEnvelope(new SimpleMessage('test'));

        $result = $this->driver->enqueue(self::Q, $m);

        $this->assertSame($m, $result);

        $this->assertSame($m, $this->driver->dequeue(self::Q));
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

    public function testMessagesCanBeEnqueuedDequeuedAndReleased()
    {
        $m = new SimpleMessage('test');
        $this->assertInstanceOf(Envelope::class, $this->driver->enqueue(self::Q, $m));

        $e = $this->driver->dequeue(self::Q);
        $this->assertSame($m, $e->unwrap());

        $this->driver->release(self::Q, $e);

        $e2 = $this->driver->dequeue(self::Q);
        $this->assertSame($e, $e2);
    }

    protected function setUp()
    {
        $this->driver = new MemoryDriver();
    }
}
