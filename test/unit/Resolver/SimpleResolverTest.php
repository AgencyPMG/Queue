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

namespace PMG\Queue\Resolver;

use PMG\Queue\SimpleMessage;

class SimpleResolverTest extends \PMG\Queue\UnitTestCase
{
    const NAME = 'TestMessage';

    private $message;

    /**
     * @expectedException PMG\Queue\Exception\HandlerNotFound
     */
    public function testHandlerForErrorsWhenNoHandlerIsFound()
    {
        $resolver = new SimpleResolver([]);
        $resolver->handlerFor($this->message);
    }

    /**
     * @expectedException PMG\Queue\Exception\InvalidHandler
     */
    public function testHandlerForErrorsWhenTheHandlerIsNotCallable()
    {
        $resolver = new SimpleResolver([
            self::NAME => 'notACallable',
        ]);

        $resolver->handlerFor($this->message);
    }

    public function testHandlerForReturnACallableWhenAHandlerIsFound()
    {
        $handler = function () { };
        $resolver = new SimpleResolver([
            self::NAME => $handler,
        ]);

        $this->assertSame($handler, $resolver->handlerFor($this->message));
    }

    protected function setUp()
    {
        $this->message = new SimpleMessage(self::NAME);
    }
}
