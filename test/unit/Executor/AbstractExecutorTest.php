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

use PMG\Queue\HandlerResolver;
use PMG\Queue\SimpleMessage;

class AbstractExecutorTest extends \PMG\Queue\UnitTestCase
{
    private $resolver, $executor;

    /**
     * @expectedException PMG\Queue\Exception\HandlerNotFound
     */
    public function testExecuteErrorsWhenNoHandlerIsFoundForAMessage()
    {
        $m = new SimpleMessage('test');
        $this->resolver->expects($this->once())
            ->method('handlerFor')
            ->with($this->identicalTo($m))
            ->willReturn(null);
        $this->executor->expects($this->never())
            ->method('executeInternal');

        $this->executor->execute($m);
    }

    public function testExecuteCallsInternalExecuteWhenAHandlerIsFound()
    {
        $handler = function () { };
        $m = new SimpleMessage('test');
        $this->resolver->expects($this->once())
            ->method('handlerFor')
            ->with($this->identicalTo($m))
            ->willReturn($handler);
        $this->executor->expects($this->once())
            ->method('executeInternal')
            ->with($this->identicalTo($m), $this->identicalTo($handler))
            ->willReturn(true);

        $this->assertTrue($this->executor->execute($m));
    }

    protected function setUp()
    {
        $this->resolver = $this->getMock(HandlerResolver::class);
        $this->executor = $this->getMockForAbstractClass(AbstractExecutor::class, [$this->resolver]);
    }
}
