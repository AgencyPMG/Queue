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

namespace PMG\Queue\Resolver;

use PMG\Queue\HandlerResolver;
use PMG\Queue\SimpleMessage;

class MappingResolverTest extends \PMG\Queue\UnitTestCase
{
    const NAME = 'TestMessage';

    private $message;

    public function testHandlerForReturnsNotFoundWhenNoHandlerIsFound()
    {
        $resolver = new MappingResolver([]);
        $this->assertEquals(HandlerResolver::NOT_FOUND, $resolver->handlerFor($this->message));
    }

    /**
     * @expectedException PMG\Queue\Exception\InvalidHandler
     */
    public function testHandlerForErrorsWhenTheHandlerIsNotCallable()
    {
        $resolver = new MappingResolver([
            self::NAME => 'notACallable',
        ]);

        $resolver->handlerFor($this->message);
    }

    public function validMaps()
    {
        $handler = function () { };
        return [
            [[self::NAME => $handler]],
            [new \ArrayObject([self::NAME => $handler])],
        ];
    }

    /**
     * @dataProvider validMaps
     */
    public function testHandlerForReturnACallableWhenAHandlerIsFound($map)
    {
        $resolver = new MappingResolver($map);

        $this->assertInternalType('callable', $resolver->handlerFor($this->message));
    }

    public function invalidMaps()
    {
        return [
            [1],
            [1.0],
            [false],
            [null],
            [new \stdClass],
        ];
    }

    /**
     * @expectedException PMG\Queue\Exception\InvalidArgumentException
     * @dataProvider invalidMaps
     */
    public function testMappingResolverCannotBeCreatedWithInvalidMapping($map)
    {
        new MappingResolver($map);
    }

    protected function setUp()
    {
        $this->message = new SimpleMessage(self::NAME);
    }
}
