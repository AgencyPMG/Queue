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

namespace PMG\Queue\Router;

class _SimpleRouterMessage implements \PMG\Queue\Message
{
    use \PMG\Queue\MessageTrait;
}

class SimpleRouterTest extends \PMG\Queue\UnitTestCase
{
    public function testRouterReturnsNullWhenNoQueueIsFound()
    {
        $this->assertNull((new SimpleRouter([]))->queueFor(new _SimpleRouterMessage()));
    }

    public function testRouterReturnsQueueNameFromMapWhenMessageIsFound()
    {
        $router = new SimpleRouter([
            _SimpleRouterMessage::class => 'ExampleQueue',
        ]);

        $this->assertEquals('ExampleQueue', $router->queueFor(new _SimpleRouterMessage()));
    }
}
