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

use PHPUnit\Framework\MockObject\MockObject;
use GuzzleHttp\Promise\FulfilledPromise;
use Psr\Log\LogLevel;
use PMG\Queue\Exception\SimpleMustStop;

class DefaultConsumerTest extends UnitTestCase
{
    const Q = 'TestQueue';

    /**
     * @var Driver&MockObject
     */
    private Driver $driver;

    /**
     * @var MessageHandler&MockObject
     */
    private MessageHandler $handler;
   
    /**
     * @var RetrySpec&MockObject
     */
    private RetrySpec $retries;

    private CollectingLogger $logger;

    private DefaultConsumer $consumer;

    private SimpleMessage $message;

    private DefaultEnvelope $envelope;

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
            ->willReturn(self::promise(true));

        $this->assertTrue($this->consumer->once(self::Q));
    }

    public function testPlainObjectMessagesCanBeHandled()
    {
        $message = new class {};
        $envelope = new DefaultEnvelope($message);
        $this->driver->expects($this->once())
            ->method('dequeue')
            ->with(self::Q)
            ->willReturn($envelope);
        $this->driver->expects($this->once())
            ->method('ack')
            ->with(self::Q, $this->identicalTo($envelope));
        $this->handler->expects($this->once())
            ->method('handle')
            ->with($this->identicalTo($message))
            ->willReturn(self::promise(true));

        $this->assertTrue($this->consumer->once(self::Q));
    }

    public function testOnceWithAFailedMessageAndValidRetryPutsTheMessageBackInTheQueue()
    {
        $this->withMessage();
        $this->willRetry();
        $this->driver->expects($this->once())
            ->method('retry')
            ->with(self::Q, $this->envelope->retry());
        $this->handler->expects($this->once())
            ->method('handle')
            ->with($this->identicalTo($this->message))
            ->willReturn(self::promise(false));

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
            ->willReturn(self::promise(false));

        $this->assertFalse($this->consumer->once(self::Q));
    }

    public function testOnceWithAExceptionThrownFromHandlerAndValidRetryRetriesJobAndThrows()
    {
        $this->withMessage();
        $this->willRetry();
        $this->driver->expects($this->once())
            ->method('retry')
            ->with(self::Q, $this->envelope->retry());
        $this->handler->expects($this->once())
            ->method('handle')
            ->with($this->identicalTo($this->message))
            ->willThrowException(new \Exception('oops'));

        $this->assertFalse($this->consumer->once(self::Q));
        $messages = $this->logger->getMessages(LogLevel::CRITICAL);

        $this->assertCount(1, $messages);
        $this->assertStringContainsString('oops', $messages[0]);
        $this->assertStringContainsString('TestMessage', $messages[0]);
    }

    public function testFailureWithMustStopAcksMessagesAndRethrows()
    {
        $this->expectException(Exception\MustStop::class);
        $this->withMessage();
        $this->driver->expects($this->once())
            ->method('ack')
            ->with(self::Q, $this->envelope);
        $this->handler->expects($this->once())
            ->method('handle')
            ->with($this->identicalTo($this->message))
            ->willThrowException(new SimpleMustStop('oops'));

        $this->consumer->once(self::Q);
    }

    public function testFailureWithShouldReleaseReleasesMessageBackIntoDriver()
    {
        $this->withMessage();
        $this->driver->expects($this->once())
            ->method('release')
            ->with(self::Q, $this->envelope);
        $this->handler->expects($this->once())
            ->method('handle')
            ->with($this->identicalTo($this->message))
            ->willThrowException(new Exception\ForkedProcessCancelled('oops'));

        $result = $this->consumer->once(self::Q);
    }

    /**
     * @group https://github.com/AgencyPMG/Queue/issues/61
     * @group lifecycles
     */
    public function testLifecycleOfSuccessfulMessageCallsExpectedLifecycleMethods()
    {
        $lifecycle = $this->createMock(MessageLifecycle::class);
        $lifecycle->expects($this->once())
            ->method('starting')
            ->with($this->identicalTo($this->message), $this->identicalTo($this->consumer));
        $lifecycle->expects($this->once())
            ->method('completed')
            ->with($this->identicalTo($this->message), $this->identicalTo($this->consumer));
        $lifecycle->expects($this->once())
            ->method('succeeded')
            ->with($this->identicalTo($this->message), $this->identicalTo($this->consumer));
        $this->withMessage();
        $this->handler->expects($this->once())
            ->method('handle')
            ->with($this->identicalTo($this->message))
            ->willReturn(self::promise(true));

        $result = $this->consumer->once(self::Q, $lifecycle);

        $this->assertTrue($result);
    }

    /**
     * @group https://github.com/AgencyPMG/Queue/issues/61
     * @group lifecycles
     */
    public function testLifecycleOnFailedMessageCallsExpectedLifecycleMethods()
    {
        $lifecycle = $this->createMock(MessageLifecycle::class);
        $lifecycle->expects($this->once())
            ->method('starting')
            ->with($this->identicalTo($this->message), $this->identicalTo($this->consumer));
        $lifecycle->expects($this->once())
            ->method('completed')
            ->with($this->identicalTo($this->message), $this->identicalTo($this->consumer));
        $lifecycle->expects($this->once())
            ->method('failed')
            ->with($this->identicalTo($this->message), $this->identicalTo($this->consumer));
        $this->withMessage();
        $this->retries->expects($this->atLeastOnce())
            ->method('canRetry')
            ->willReturn(false);
        $this->handler->expects($this->once())
            ->method('handle')
            ->with($this->identicalTo($this->message))
            ->willReturn(self::promise(false));

        $result = $this->consumer->once(self::Q, $lifecycle);

        $this->assertFalse($result);
    }

    /**
     * @group https://github.com/AgencyPMG/Queue/issues/69
     * @group lifecycles
     */
    public function testLifecycleOnRetryingMessageCallsExpectedLifecycleMethods()
    {
        $lifecycle = $this->createMock(MessageLifecycle::class);
        $lifecycle->expects($this->once())
            ->method('starting')
            ->with($this->identicalTo($this->message), $this->identicalTo($this->consumer));
        $lifecycle->expects($this->once())
            ->method('completed')
            ->with($this->identicalTo($this->message), $this->identicalTo($this->consumer));
        $lifecycle->expects($this->once())
            ->method('retrying')
            ->with($this->identicalTo($this->message), $this->identicalTo($this->consumer));
        $this->withMessage();
        $this->willRetry();
        $this->handler->expects($this->once())
            ->method('handle')
            ->with($this->identicalTo($this->message))
            ->willReturn(self::promise(false));

        $result = $this->consumer->once(self::Q, $lifecycle);

        $this->assertFalse($result);
    }

    /**
     * @group https://github.com/AgencyPMG/Queue/issues/58
     */
    public function testRetriedMessagesUseTheDelayFromTheRetrySpec()
    {
        $this->withMessage();
        $this->willRetry();
        $this->retries->expects($this->once())
            ->method('retryDelay')
            ->with($this->envelope)
            ->willReturn(10);
        $this->driver->expects($this->once())
            ->method('retry')
            ->with(self::Q, $this->envelope->retry(10));
        $this->handler->expects($this->once())
            ->method('handle')
            ->with($this->identicalTo($this->message))
            ->willReturn(self::promise(false));

        $this->consumer->once(self::Q);
    }

    protected function setUp() : void
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

    private static function promise(bool $result) : FulfilledPromise
    {
        return new FulfilledPromise($result);
    }
}
