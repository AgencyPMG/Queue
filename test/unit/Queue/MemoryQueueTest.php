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
use PMG\Queue\RetrySpec;

class MemoryQueueTest extends \PMG\Queue\UnitTestCase
{
    private $retries, $queue;

    public function testMessagesCanBeEnqueuedAndDequeued()
    {
        $this->willRetry(true);
        $msg = $this->getMock(Message::class);

        $this->queue->enqueue($msg);

        $this->assertCount(1, $this->queue, 'should have one message in the queue');
        $this->assertSame($msg, $this->queue->dequeue());
        $this->assertCount(0, $this->queue, 'should have no messages in the queue');

        $this->queue->fail($msg);
        $this->assertCount(1, $this->queue, 'the message should have gone back on to the queue');
    }

    public function testMessagesDoNotGoBackIntoTheQueueWhenTheyCannotBeRetried()
    {
        $this->willRetry(false);
        $msg = $this->getMock(Message::class);

        $this->queue->enqueue($msg);

        $this->assertCount(1, $this->queue, 'should have one message in the queue');
        $this->assertSame($msg, $this->queue->dequeue());
        $this->assertCount(0, $this->queue, 'should have no messages in the queue');

        $this->queue->fail($msg);
        $this->assertCount(0, $this->queue, 'cannot retry message');
    }

    public function testEmptyQueueReturnsNullWhenNoMessagesAreFound()
    {
        $this->assertNull((new MemoryQueue())->dequeue());
    }

    protected function setUp()
    {
        $this->retries = $this->getMock(RetrySpec::class);
        $this->queue = new MemoryQueue($this->retries);
    }

    private function willRetry($bool)
    {
        $this->retries->expects($this->atLeastOnce())
            ->method('canRetry')
            ->willReturn($bool);
    }
}
