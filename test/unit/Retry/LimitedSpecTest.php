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

namespace PMG\Queue\Retry;

use PMG\Queue\DefaultEnvelope;
use PMG\Queue\SimpleMessage;
use PMG\Queue\Exception\InvalidArgumentException;

class LimitedSpecTest extends \PMG\Queue\UnitTestCase
{
    public static function badAttempts()
    {
        return [
            [0],
            [-1],
        ];
    }

    /**
     * @dataProvider badAttempts
     */
    public function testCreatingLimitSpecFailsWithInvalidAttempts($attempts)
    {
        $this->expectException(InvalidArgumentException::class);

        new LimitedSpec($attempts);
    }

    public function testCreatingLimitSpecFailsWithInvalidDelay()
    {
        $this->expectException(InvalidArgumentException::class);

        new LimitedSpec(5, -5);
    }

    public function testCanRetryReturnsTrueWhenAttemptsAreBelowLimit()
    {
        $spec = new LimitedSpec();

        $env = new DefaultEnvelope(new SimpleMessage('test'));

        $this->assertTrue($spec->canRetry($env));
    }

    public function testCanRetryReturnsFalseWhenAttemptsExceedTheLimit()
    {
        $spec = new LimitedSpec(1);

        $env = new DefaultEnvelope(new SimpleMessage('test'), 1);

        $this->assertFalse($spec->canRetry($env));
    }

    public function testRetryDelayReturnsZeroWithoutSpecifiedDelay()
    {
        $spec = new LimitedSpec();

        $env = new DefaultEnvelope(new SimpleMessage('test'));

        $this->assertSame(0, $spec->retryDelay($env));
    }

    public function testRetryDelayReturnsAsConfigured()
    {
        $spec = new LimitedSpec(null, $expectedDelay = rand(1, 60));

        $env = new DefaultEnvelope(new SimpleMessage('test'));

        $this->assertSame($expectedDelay, $spec->retryDelay($env));
    }
}
