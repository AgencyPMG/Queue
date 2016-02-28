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

namespace PMG\Queue;

use Psr\Log\LogLevel;

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

        $this->assertNull($this->consumer->once(self::Q));
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

        $this->assertTrue($this->consumer->once(self::Q));
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

        $this->assertFalse($this->consumer->once(self::Q));
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

        $this->assertFalse($this->consumer->once(self::Q));
    }

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

        $this->assertFalse($this->consumer->once(self::Q));
        $messages = $this->logger->getMessages(LogLevel::CRITICAL);

        $this->assertCount(1, $messages);
        $this->assertContains('oops', $messages[0]);
        $this->assertContains('TestMessage', $messages[0]);
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

    public function testRunStopsWhenADriverErrorIsThrown()
    {
        $this->driver->expects($this->once())
            ->method('dequeue')
            ->with(self::Q)
            ->willThrowException(new Exception\SerializationError('broke'));

        $result = $this->consumer->run(self::Q);
        $messages = $this->logger->getMessages(LogLevel::EMERGENCY);

        $this->assertEquals(DefaultConsumer::EXIT_ERROR, $result);
        $this->assertCount(1, $messages);
        $this->assertContains('broke', $messages[0]);
    }

    protected function setUp()
    {
        $this->driver = $this->getMock(Driver::class);
        $this->executor = $this->getMock(MessageExecutor::class);
        $this->retries = $this->getMock(RetrySpec::class);
        $this->logger = new CollectingLogger();
        $this->consumer = new DefaultConsumer($this->driver, $this->executor, $this->retries, $this->logger);
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
