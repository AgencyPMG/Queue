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

use PMG\Queue\SimpleMessage;

class SimpleRouterTest extends \PMG\Queue\UnitTestCase
{
    public function testQueueForReturnsTheQueueNamePassedToTheConstructor()
    {
        $r = new SimpleRouter('queue');

        $this->assertEquals('queue', $r->queueFor(new SimpleMessage('test')));
    }
}
