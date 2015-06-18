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

namespace PMG\Queue\Retry;

use PMG\Queue\DefaultEnvelope;
use PMG\Queue\SimpleMessage;

class LimitSpecTest extends \PMG\Queue\UnitTestCase
{
    public static function badAttempts()
    {
        return [
            [0],
            [-1],
        ];
    }

    /**
     * @expectedException InvalidArgumentException
     * @dataProvider badAttempts
     */
    public function testCreatingLimitSpecFailsWithInvalidAttempts($attempts)
    {
        new LimitedSpec($attempts);
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
}
