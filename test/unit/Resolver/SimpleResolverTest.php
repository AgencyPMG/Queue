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

use PMG\Queue\SimpleMessage;

class SimpleResolverTest extends \PMG\Queue\UnitTestCase
{
    public function testHandlerForReturnsTheSameCallablePassToConstructor()
    {
        $handler = function () { };
        $resolver = new SimpleResolver($handler);

        $this->assertSame($handler, $resolver->handlerFor(new SimpleMessage('t')));
    }
}
