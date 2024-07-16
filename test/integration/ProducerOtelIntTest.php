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

use LogicException;
use OpenTelemetry\API\Trace\SpanKind;
use OpenTelemetry\API\Trace\StatusCode;
use OpenTelemetry\SemConv\TraceAttributes;
use PMG\Queue\Otel\PmgQueueInstrumentation;
use PMG\Queue\Exception\SimpleMustStop;

/**
 * @requires extension opentelemetry
 */
class ProducerOtelIntTest extends OtelIntegrationTestCase
{
    const Q = 'test';

    private Driver $driver;
    private Producer $producer;

    public function testSendingMessagesProducesSpans() : void
    {
        $this->producer->send(new SimpleMessage('test'));

        $this->assertCount(1, $this->spans);
        $span = $this->spans[0];
        $this->assertSame(self::Q.' publish', $span->getName());
        $status = $span->getStatus();
        $this->assertSame(StatusCode::STATUS_UNSET, $status->getCode());
        $attr = $span->getAttributes();
        $this->assertSame(self::Q, $attr->get(TraceAttributes::MESSAGING_DESTINATION_NAME));
        $this->assertSame('publish', $attr->get(PmgQueueInstrumentation::OPERATION_TYPE));
        $this->assertSame('enqueue', $attr->get(PmgQueueInstrumentation::OPERATION_NAME));
    }

    public function testErrorsDuringSendMarkSpansAsErrored() : void
    {
        $driver = $this->createMock(Driver::class);
        $driver->expects($this->once())
            ->method('enqueue')
            ->willThrowException(new LogicException('ope'));
        $producer = new DefaultProducer(
            $driver,
            new Router\SimpleRouter(self::Q)
        );

        $e = null;
        try {
            $producer->send(new SimpleMessage('test'));
        } catch (LogicException $e) {
        }

        $this->assertInstanceOf(LogicException::class, $e);
        $this->assertCount(1, $this->spans);
        $span = $this->spans[0];
        $this->assertSame(self::Q.' publish', $span->getName());
        $status = $span->getStatus();
        $this->assertSame(StatusCode::STATUS_ERROR, $status->getCode());
        $this->assertSame('ope', $status->getDescription());
    }

    protected function setUp() : void
    {
        parent::setUp();
        $this->driver = new Driver\MemoryDriver();
        $this->producer = new DefaultProducer(
            $this->driver,
            new Router\SimpleRouter(self::Q)
        );
    }
}
