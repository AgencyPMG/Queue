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

namespace PMG\Queue\Otel;

use function OpenTelemetry\Instrumentation\hook;
use OpenTelemetry\API\Instrumentation\CachedInstrumentation;
use OpenTelemetry\API\Trace\Span;
use OpenTelemetry\API\Trace\SpanBuilderInterface;
use OpenTelemetry\API\Trace\SpanKind;
use OpenTelemetry\API\Trace\StatusCode;
use OpenTelemetry\Context\Context;
use OpenTelemetry\SemConv\TraceAttributes;
use PMG\Queue\Consumer;
use PMG\Queue\Driver;
use PMG\Queue\Envelope;

final class PmgQueueInstrumentation
{
    public const NAME = 'pmg-queue';
    public const INSTRUMENTATION_NAME = 'com.pmg.opentelemetry.'.self::NAME;

    // these two are in semconv, but have not yet maded it to the PHP SDK
    // type is generic and defined in semconv where name is system specific
    public const OPERATION_TYPE = 'messaging.operation.type';
    public const OPERATION_NAME = 'messaging.operation.name';

    public static bool $registered = false;

    public static function register(): bool
    {
        if (self::$registered) {
            return false;
        }

        if (!extension_loaded('opentelemetry')) {
            return false;
        }

        self::$registered = true;

        $instrumentation = new CachedInstrumentation(self::INSTRUMENTATION_NAME);

        hook(
            Consumer::class,
            'once',
            pre: static function (
                Consumer $consumer,
                array $params,
                string $class,
                string $function,
                ?string $filename,
                ?int $lineno,
            ) use ($instrumentation): array {
                $queueName = $params[0];
                assert(is_string($queueName));

                $builder = $instrumentation
                    ->tracer()
                    ->spanBuilder($queueName.' receive')
                    ->setSpanKind(SpanKind::KIND_CONSUMER)
                    ->setAttribute(TraceAttributes::CODE_FUNCTION, $function)
                    ->setAttribute(TraceAttributes::CODE_NAMESPACE, $class)
                    ->setAttribute(TraceAttributes::CODE_FILEPATH, $filename)
                    ->setAttribute(TraceAttributes::CODE_LINENO, $lineno)
                    ->setAttribute(TraceAttributes::MESSAGING_DESTINATION_NAME, $queueName)
                    ->setAttribute(self::OPERATION_TYPE, 'receive') // generic
                    ->setAttribute(self::OPERATION_NAME, 'once') // system specific
                ;

                $parent = Context::getCurrent();
                $span = $builder
                    ->setParent($parent)
                    ->startSpan();

                $context = $span->storeInContext($parent);
                Context::storage()->attach($context);

                return $params;
            },
            post: static function (
                Consumer $consumer,
                array $params,
                mixed $result,
                ?\Throwable $exception
            ): void {
                $scope = Context::storage()->scope();
                if (null === $scope) {
                    return;
                }

                $queueName = $params[0];
                assert(is_string($queueName));

                $scope->detach();
                $span = Span::fromContext($scope->context());

                if (null !== $exception) {
                    $span->recordException($exception, [
                        TraceAttributes::EXCEPTION_ESCAPED => true,
                    ]);
                    $span->setStatus(StatusCode::STATUS_ERROR, $exception->getMessage());
                } elseif ($result === false) {
                    $span->setStatus(StatusCode::STATUS_ERROR, 'Message was not handled successfully');
                }

                $span->end();
            }
        );

        hook(
            Driver::class,
            'enqueue',
            pre: static function (
                Driver $bus,
                array $params,
                string $class,
                string $function,
                ?string $filename,
                ?int $lineno,
            ) use ($instrumentation): array {
                $queueName = $params[0];
                assert(is_string($queueName));

                $message = $params[1];
                assert(is_object($message));

                $builder = $instrumentation
                    ->tracer()
                    ->spanBuilder($queueName.' publish')
                    ->setSpanKind(SpanKind::KIND_PRODUCER)
                    ->setAttribute(TraceAttributes::CODE_FUNCTION, $function)
                    ->setAttribute(TraceAttributes::CODE_NAMESPACE, $class)
                    ->setAttribute(TraceAttributes::CODE_FILEPATH, $filename)
                    ->setAttribute(TraceAttributes::CODE_LINENO, $lineno)
                    ->setAttribute(TraceAttributes::MESSAGING_DESTINATION_NAME, $queueName)
                    ->setAttribute(self::OPERATION_TYPE, 'publish')
                    ->setAttribute(self::OPERATION_NAME, 'enqueue')
                    ;

                $parent = Context::getCurrent();
                $span = $builder
                    ->setParent($parent)
                    ->startSpan();

                $context = $span->storeInContext($parent);
                Context::storage()->attach($context);

                return $params;
            },
            post: static function (
                Driver $driver,
                array $params,
                ?Envelope $envelope,
                ?\Throwable $exception
            ): void {
                $scope = Context::storage()->scope();
                if (null === $scope) {
                    return;
                }

                $scope->detach();
                $span = Span::fromContext($scope->context());

                if (null !== $exception) {
                    $span->recordException($exception, [
                        TraceAttributes::EXCEPTION_ESCAPED => true,
                    ]);
                    $span->setStatus(StatusCode::STATUS_ERROR, $exception->getMessage());
                }

                $span->end();
            }
        );

        return self::$registered;
    }
}
