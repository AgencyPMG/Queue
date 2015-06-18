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

namespace PMG\Queue\Executor;

use PMG\Queue\HandlerResolver;
use PMG\Queue\SimpleMessage;

class SingleThreadExecutorTest extends \PMG\Queue\UnitTestCase
{
    private $resolver, $executor;

    public function testExecutorLooksUpHandlerAndRunsIt()
    {
        $msg = new SimpleMessage('TestMessage');
        $called = false;
        $this->resolver->expects($this->once())
            ->method('handlerFor')
            ->with($this->identicalTo($msg))
            ->willReturn(function ($m) use ($msg, &$called) {
                $called = true;
                $this->assertSame($msg, $m);
            });

        $this->assertTrue($this->executor->execute($msg));

        $this->assertTrue($called, 'executor should have called handler function');
    }

    protected function setUp()
    {
        $this->resolver = $this->getMock(HandlerResolver::class);
        $this->executor = new SimpleExecutor($this->resolver);
    }
}
