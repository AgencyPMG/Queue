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

namespace PMG\Queue\Router;

use PMG\Queue\Router;
use PMG\Queue\SimpleMessage;

class FallbackRouterTest extends \PMG\Queue\UnitTestCase
{
    const FALLBACK = 'FallbackQueue';

    private $wrapped, $router, $message;

    public function testQueueForReturnsValueFromWrappedRouterWhenFound()
    {
        $this->wrappedRouterReturns('FoundQueue');

        $this->assertEquals('FoundQueue', $this->router->queueFor($this->message));
    }

    public function testQueueForReturnsFallbackValueWhenWrappedWRouterFindsNothing()
    {
        $this->wrappedRouterReturns(null);

        $this->assertEquals(self::FALLBACK, $this->router->queueFor($this->message));
    }

    protected function setUp()
    {
        $this->wrapped = $this->createMock(Router::class);
        $this->router = new FallbackRouter($this->wrapped, self::FALLBACK);
        $this->message = new SimpleMessage('test');
    }

    private function wrappedRouterReturns($value)
    {
        $this->wrapped->expects($this->once())
            ->method('queueFor')
            ->with($this->identicalTo($this->message))
            ->willReturn($value);
    }
}
