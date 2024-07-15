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

use PMG\Queue\Otel\PmgQueueInstrumentation;
use OpenTelemetry\API\Trace\Span;
use OpenTelemetry\Context\Context;
use OpenTelemetry\SemConv\TraceAttributes;
use OpenTelemetry\SDK\Sdk;

// look for deps and if we have them all we'll load the instrumentation.
if (
    !extension_loaded('opentelemetry') ||
    !class_exists(Span::class) ||
    !class_exists(Context::class) ||
    !interface_exists(TraceAttributes::class)
) {
    return;
}


// allow disabling instrumentation via the SDK's supported environment variables
if (class_exists(Sdk::class) && Sdk::isInstrumentationDisabled(PmgQueueInstrumentation::NAME)) {
    return;
}

PmgQueueInstrumentation::register();
