<?php
/**
 * This file is part of PMG\Queue
 *
 * Copyright (c) PMG <https://www.pmg.com>
 *
 * For full copyright information see the LICENSE file distributed
 * with this source code.
 *
 * @license     http://opensource.org/licenses/MIT MIT
 */

namespace PMG\Queue\Executor;

use PMG\Queue\SimpleMessage;
use PMG\Queue\Resolver\SimpleResolver;

/**
 * @requires extension pcntl
 */
class ForkingExecutorTest extends \PMG\Queue\UnitTestCase
{
    private $resolver, $executor;

    public function testExecutorReturnsTrueWhenTheChildProcessExitsSuccessfully()
    {
        $resolver = new SimpleResolver(function () {
            // noop;
        });
        $exec = new ForkingExecutor($resolver);

        $this->assertTrue($exec->execute(new SimpleMessage('TestMessage')));
    }

    public function testExecutorReturnsFalsWhenTheChildProcessExistUnsuccessfully()
    {
        $resolver = new SimpleResolver(function () {
            exit(1);
        });
        $exec = new ForkingExecutor($resolver);

        $this->assertFalse($exec->execute(new SimpleMessage('TestMessage')));
    }
}
