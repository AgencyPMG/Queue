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

class AbstractConsumerTest extends UnitTestCase
{
    const Q = 'TestQueue';

    private $logger, $consumer, $message, $envelope;

    /**
     * This is a bad test: lots of stuff going on, but because we 
     * don't want to block forever, it's the best we have.
     */
    public function testRunConsumesMessagesUntilConsumerIsStopped()
    {
        $this->consumer->expects($this->at(0))
            ->method('once')
            ->with(self::Q);
        $this->consumer->expects($this->at(1))
            ->method('once')
            ->with(self::Q)
            ->willThrowException(new Exception\SimpleMustStop('oops', 1));

        $this->assertEquals(1, $this->consumer->run(self::Q));
    }

    public function testRunStopsWhenADriverErrorIsThrown()
    {
        $this->consumer->expects($this->at(0))
            ->method('once')
            ->with(self::Q);
        $this->consumer->expects($this->at(1))
            ->method('once')
            ->with(self::Q)
            ->willThrowException(new Exception\SerializationError('broke'));

        $result = $this->consumer->run(self::Q);
        $messages = $this->logger->getMessages(LogLevel::EMERGENCY);

        $this->assertEquals(DefaultConsumer::EXIT_ERROR, $result);
        $this->assertCount(1, $messages);
        $this->assertContains('broke', $messages[0]);
    }

    /**
     * @group https://github.com/AgencyPMG/Queue/issues/31
     */
    public function testRunStopsWhenAThrowableisCaught()
    {
        $this->consumer->expects($this->at(0))
            ->method('once')
            ->with(self::Q);
        $this->consumer->expects($this->at(1))
            ->method('once')
            ->with(self::Q)
            ->willReturnCallback(function () {
                // phpunit can't take an `Error` class in `willThrowException`
                throw new \Error('oops');
            });

        $result = $this->consumer->run(self::Q);
        $messages = $this->logger->getMessages(LogLevel::EMERGENCY);

        $this->assertEquals(DefaultConsumer::EXIT_ERROR, $result);
        $this->assertCount(1, $messages);
        $this->assertContains('oops', $messages[0]);
    }

    public function testConsumerWithoutLoggerPassedInCreatesANullLoggerOnDemand()
    {
        $consumer = $this->getMockForAbstractClass(AbstractConsumer::class);
        $consumer->expects($this->once())
            ->method('once')
            ->with(self::Q)
            ->willThrowException(new Exception\SerializationError('broke'));

        $result = $consumer->run(self::Q);

        $this->assertEquals(DefaultConsumer::EXIT_ERROR, $result);
    }

    protected function setUp()
    {
        $this->logger = new CollectingLogger();
        $this->consumer = $this->getMockForAbstractClass(AbstractConsumer::class, [$this->logger]);
        $this->message = new SimpleMessage('TestMessage');
        $this->envelope = new DefaultEnvelope($this->message);
    }
}
