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

namespace PMG\Queue;

use Psr\Log\LogLevel;

class DefaultConsumerTest extends UnitTestCase
{
    const Q = 'TestQueue';

    private $driver, $handler, $retries, $consumer;

    public function testOnceDoesNothingWhenTheQueueIsEmpty()
    {
        $this->driver->expects($this->once())
            ->method('dequeue')
            ->with(self::Q)
            ->willReturn(null);
        $this->handler->expects($this->never())
            ->method('handle');

        $this->assertNull($this->consumer->once(self::Q));
    }

    public function testOnceExecutesTheMessageAndAcknowledgesIt()
    {
        $this->withMessage();
        $this->driver->expects($this->once())
            ->method('ack')
            ->with(self::Q, $this->identicalTo($this->envelope));
        $this->handler->expects($this->once())
            ->method('handle')
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
        $this->handler->expects($this->once())
            ->method('handle')
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
        $this->handler->expects($this->once())
            ->method('handle')
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
        $this->handler->expects($this->once())
            ->method('handle')
            ->with($this->identicalTo($this->message))
            ->willThrowException(new \Exception('oops'));

        $this->assertFalse($this->consumer->once(self::Q));
        $messages = $this->logger->getMessages(LogLevel::CRITICAL);

        $this->assertCount(1, $messages);
        $this->assertContains('oops', $messages[0]);
        $this->assertContains('TestMessage', $messages[0]);
    }

    protected function setUp()
    {
        $this->driver = $this->createMock(Driver::class);
        $this->handler = $this->createMock(MessageHandler::class);
        $this->retries = $this->createMock(RetrySpec::class);
        $this->logger = new CollectingLogger();
        $this->consumer = new DefaultConsumer($this->driver, $this->handler, $this->retries, $this->logger);
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
