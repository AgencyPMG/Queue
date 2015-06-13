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

class _ConsumerDriverError extends \Exception implements Exception\DriverError
{

}

class DefaultConsumerTest extends UnitTestCase
{
    const Q = 'TestQueue';

    private $driver, $executor, $retries, $consumer;

    public function testOnceDoesNothingWhenTheQueueIsEmpty()
    {
        $this->driver->expects($this->once())
            ->method('dequeue')
            ->with(self::Q)
            ->willReturn(null);
        $this->executor->expects($this->never())
            ->method('execute');

        $this->consumer->once(self::Q);
    }

    public function testOnceExecutesTheMessageAndAcknowledgesIt()
    {
        $this->withMessage();
        $this->driver->expects($this->once())
            ->method('ack')
            ->with(self::Q, $this->identicalTo($this->envelope));
        $this->executor->expects($this->once())
            ->method('execute')
            ->with($this->identicalTo($this->message))
            ->willReturn(true);

        $this->consumer->once(self::Q);
    }

    public function testOnceWithAFailedMessageAndValidRetryPutsTheMessageBackInTheQueue()
    {
        $this->withMessage();
        $this->willRetry();
        $this->driver->expects($this->once())
            ->method('retry')
            ->with(self::Q, $this->identicalTo($this->envelope));
        $this->executor->expects($this->once())
            ->method('execute')
            ->with($this->identicalTo($this->message))
            ->willReturn(false);

        $this->consumer->once(self::Q);
    }

    public function testFailedMessageThatCannotBeRetriedIsNotPutBackInTheQueue()
    {
        $this->withMessage();
        $this->retries->expects($this->once())
            ->method('canRetry')
            ->willReturn(false);
        $this->driver->expects($this->never())
            ->method('retry');
        $this->executor->expects($this->once())
            ->method('execute')
            ->with($this->identicalTo($this->message))
            ->willReturn(false);

        $this->consumer->once(self::Q);
    }

    /**
     * @expectedException PMG\Queue\Exception\MessageFailed
     */
    public function testOnceWithAExceptionThrownFromExecutorAndValidRetryRetriesJobAndThrows()
    {
        $this->withMessage();
        $this->willRetry();
        $this->driver->expects($this->once())
            ->method('retry')
            ->with(self::Q, $this->identicalTo($this->envelope));
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
        $this->willRetry();
        $this->driver->expects($this->exactly(3))
            ->method('dequeue')
            ->willReturn($this->envelope);
        $this->driver->expects($this->once())
            ->method('ack');
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
            ->willThrowException(new Exception\SimpleMustStop('oops', 1));

        $this->assertEquals(1, $this->consumer->run(self::Q));
    }

    /**
     * @expectedException PMG\Queue\_ConsumerDriverError
     */
    public function testRunStopsWhenADriverErrorIsThrown()
    {
        $this->driver->expects($this->once())
            ->method('dequeue')
            ->with(self::Q)
            ->willThrowException(new _ConsumerDriverError('broke'));

        $this->consumer->run(self::Q);
    }

    protected function setUp()
    {
        $this->driver = $this->getMock(Driver::class);
        $this->executor = $this->getMock(MessageExecutor::class);
        $this->retries = $this->getMock(RetrySpec::class);
        $this->consumer = new DefaultConsumer($this->driver, $this->executor, $this->retries);
        $this->message = new SimpleMessage('TestMessage');
        $this->envelope = new DefaultEnvelope($this->message);
    }

    private function withMessage()
    {
        $this->driver->expects($this->once())
            ->method('dequeue')
            ->with(self::Q)
            ->willReturn($this->envelope);
    }

    private function willRetry()
    {
        $this->retries->expects($this->atLeastOnce())
            ->method('canRetry')
            ->willReturn(true);
    }
}
