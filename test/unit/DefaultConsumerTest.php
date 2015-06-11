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

class _ConsumerTestStop extends \Exception implements Exception\MustStop
{
    // noop
}

class DefaultConsumerTest extends UnitTestCase
{
    const Q = 'TestQueue';

    private $queues, $queue, $executor, $consumer;

    public function testOnceDoesNothingWhenTheQueueIsEmpty()
    {
        $this->queue->expects($this->once())
            ->method('dequeue')
            ->willReturn(null);
        $this->executor->expects($this->never())
            ->method('execute');

        $this->consumer->once(self::Q);
    }

    public function testOnceExecutesTheMessageAndAcknowledgesIt()
    {
        $this->withMessage();
        $this->queue->expects($this->once())
            ->method('ack')
            ->with($this->identicalTo($this->message));
        $this->executor->expects($this->once())
            ->method('execute')
            ->with($this->identicalTo($this->message))
            ->willReturn(true);

        $this->consumer->once(self::Q);
    }

    public function testOnceWithAFailedMessagePutsTheMessageBackInTheQueue()
    {
        $this->withMessage();
        $this->queue->expects($this->once())
            ->method('fail')
            ->with($this->identicalTo($this->message));
        $this->executor->expects($this->once())
            ->method('execute')
            ->with($this->identicalTo($this->message))
            ->willReturn(false);

        $this->consumer->once(self::Q);
    }

    /**
     * @expectedException PMG\Queue\Exception\MessageFailed
     */
    public function testOnceWithAExceptionThrownFromExecutorFailsAndRethrows()
    {
        $this->withMessage();
        $this->queue->expects($this->once())
            ->method('fail')
            ->with($this->identicalTo($this->message));
        $this->executor->expects($this->once())
            ->method('execute')
            ->with($this->identicalTo($this->message))
            ->willThrowException(new \Exception('oops'));

        $this->consumer->once(self::Q);
    }

    /**
     * This is a bad test: lots of stuff going on, but because we 
     * don't want to block forever, it's the best we have.
     */
    public function testRunConsumesMessagesUntilConsumerIsStopped()
    {
        $this->queue->expects($this->exactly(3))
            ->method('dequeue')
            ->willReturn($this->message);
        $this->queue->expects($this->once())
            ->method('ack');
        $this->queue->expects($this->exactly(2))
            ->method('fail');
        $this->executor->expects($this->at(0))
            ->method('execute')
            ->with($this->identicalTo($this->message))
            ->willReturn(true);
        $this->executor->expects($this->at(1))
            ->method('execute')
            ->with($this->identicalTo($this->message))
            ->willThrowException(new \RuntimeException('oops'));
        $this->executor->expects($this->at(2))
            ->method('execute')
            ->with($this->identicalTo($this->message))
            ->willThrowException(new _ConsumerTestStop('oops'));

        $this->consumer->run(self::Q);
    }

    protected function setUp()
    {
        $this->queue = $this->getMock(Queue::class);
        $this->queues = $this->getMock(QueueFactory::class);
        $this->executor = $this->getMock(MessageExecutor::class);
        $this->consumer = new DefaultConsumer($this->queues, $this->executor);
        $this->message = new SimpleMessage('TestMessage');

        $this->queues->expects($this->atLeastOnce())
            ->method('forName')
            ->with(self::Q)
            ->willReturn($this->queue);
    }

    private function withMessage()
    {
        $this->queue->expects($this->once())
            ->method('dequeue')
            ->willReturn($this->message);
    }
}
