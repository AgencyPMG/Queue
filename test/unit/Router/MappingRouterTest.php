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

use PMG\Queue\SimpleMessage;

class MappingRouterTest extends \PMG\Queue\UnitTestCase
{
    const NAME = 'TestMessage';

    public function testRouterReturnsNullWhenNoQueueIsFound()
    {
        $this->assertNull((new MappingRouter([]))->queueFor(new SimpleMessage(self::NAME)));
    }

    public function validMappings()
    {
        return [
            [[self::NAME => 'ExampleQueue']],
            [new \ArrayObject([self::NAME => 'ExampleQueue'])],
        ];
    }

    /**
     * @dataProvider validMappings
     */
    public function testRouterReturnsQueueNameFromMapWhenMessageIsFound($map)
    {
        $router = new MappingRouter($map);

        $this->assertEquals('ExampleQueue', $router->queueFor(new SimpleMessage(self::NAME)));
    }

    public function invalidMappings()
    {
        return [
            [1],
            [1.1],
            [false],
            [null],
            [new \stdClass],
        ];
    }

    /**
     * @dataProvider invalidMappings
     * @expectedException PMG\Queue\Exception\InvalidArgumentException
     */
    public function testRouterCannotBeCreatedWithInvalidMapping($map)
    {
        new MappingRouter($map);
    }
}
