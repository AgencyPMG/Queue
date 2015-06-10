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

namespace PMG\Queue\Queue;

use PMG\Queue\Message;

class MemoryQueueTest extends \PMG\Queue\UnitTestCase
{
    public function testMessagesCanBeEnqueuedAndDequeued()
    {
        $msg = $this->getMock(Message::class);
        $q = new MemoryQueue();

        $q->enqueue($msg);

        $this->assertCount(1, $q, 'should have one message in the queue');
        $this->assertSame($msg, $q->dequeue());
        $this->assertCount(0, $q, 'should have no messages in the queue');
    }

    public function testEmptyQueueReturnsNullWhenNoMessagesAreFound()
    {
        $this->assertNull((new MemoryQueue())->dequeue());
    }
}
