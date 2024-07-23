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

use OpenTelemetry\API\Trace\SpanKind;
use OpenTelemetry\API\Trace\StatusCode;
use OpenTelemetry\SemConv\TraceAttributes;
use PMG\Queue\Otel\PmgQueueInstrumentation;
use PMG\Queue\Exception\SimpleMustStop;

/**
 * @requires extension opentelemetry
 */
class ConsumerOtelIntTest extends OtelIntegrationTestCase
{
    const Q = 'test';

    private Driver $driver;
    private Producer $producer;

    public function testConsumerOnceEmitsSpansWhenNoMessagesAreHandled() : void
    {
        $called = false;
        $consumer = $this->createConsumer(function () use (&$called) {
            $called = true;
            return false;
        });

        $result = $consumer->once(self::Q);

        $this->assertNull($result);
        $this->assertFalse($called);
        $this->assertCount(1, $this->spans);
        $span = $this->spans[0];
        $this->assertSame(self::Q.' empty-receive', $span->getName());
        $attr = $span->getAttributes();
        $this->assertSame(self::Q, $attr->get(TraceAttributes::MESSAGING_DESTINATION_NAME));
        $this->assertSame('receive', $attr->get(PmgQueueInstrumentation::OPERATION_TYPE));
        $this->assertSame('once', $attr->get(PmgQueueInstrumentation::OPERATION_NAME));
    }

    public function testSuccessfullyHandledMessagesDoNotSetSpanStatusCode() : void
    {
        // this will produce a span
        $this->producer->send(new SimpleMessage('test'));
        $called = false;
        $consumer = $this->createConsumer(function () use (&$called) : bool {
            $called = true;
            return true;
        });

        $result = $consumer->once(self::Q);

        $this->assertTrue($result);
        $this->assertTrue($called);
        $this->assertCount(2, $this->spans, 'one span for the enqueue one for consume');
        $span = $this->spans[1];
        $this->assertSame(self::Q.' receive', $span->getName());
        $status = $span->getStatus();
        $this->assertSame(StatusCode::STATUS_UNSET, $status->getCode());
    }

    public function testUnsuccessfulMessagesSetSpanStatusAsErrored() : void
    {
        $this->producer->send(new SimpleMessage('test'));
        $called = false;
        $consumer = $this->createConsumer(function () use (&$called) : bool {
            $called = true;
            return false;
        });

        $result = $consumer->once(self::Q);

        $this->assertFalse($result);
        $this->assertTrue($called);
        $this->assertCount(2, $this->spans, 'one span for the enqueue one for consume');
        $span = $this->spans[1];
        $this->assertSame(self::Q.' receive', $span->getName());
        $status = $span->getStatus();
        $this->assertSame(StatusCode::STATUS_ERROR, $status->getCode());
        $this->assertStringContainsStringIgnoringCase(
            'not handled successfully',
            $status->getDescription()
        );
    }

    public function testErrorsDuringOnceMarkSpanAsErrored() : void
    {
        // this will produce a span
        $this->producer->send(new SimpleMessage('test'));
        $consumer = $this->createConsumer(function () {
            throw new SimpleMustStop('oh noz');
        });

        $e = null;
        try {
            $consumer->once(self::Q);
        } catch (SimpleMustStop $e) {
        }

        $this->assertInstanceOf(SimpleMustStop::class, $e);
        $this->assertCount(2, $this->spans, 'one span for the enqueue one for consume');
        $span = $this->spans[1];
        $this->assertSame(self::Q.' receive', $span->getName());
        $status = $span->getStatus();
        $this->assertSame(StatusCode::STATUS_ERROR, $status->getCode());
        $this->assertSame('oh noz', $status->getDescription());
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

    private function createConsumer(callable $handler) : DefaultConsumer
    {
        return new DefaultConsumer(
            $this->driver,
            new Handler\CallableHandler($handler),
            new Retry\NeverSpec()
        );
    }
}
