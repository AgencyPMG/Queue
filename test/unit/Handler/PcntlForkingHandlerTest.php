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

namespace PMG\Queue\Handler;

use PMG\Queue\SimpleMessage;

/**
 * This uses `CallableHandler` simply because I'm not sure how phpunit mock objects
 * behave in forked processes.
 *
 * @requires extension pcntl
 */
class PcntlForkingHandlerTest extends \PMG\Queue\UnitTestCase
{
    const NAME = 'TestMessage';

    private $message;

    public function testChildProcessWithASuccessfulResultReturnsTrueFromParent()
    {
        $handler = $this->createHandler(function () {
            return true;
        });

        $this->assertTrue($handler->handle($this->message));
    }

    public function testChildProcessWithFailedResultReturnsFalseFromTheParent()
    {
        $handler = $this->createHandler(function () {
            return false;
        });

        $this->assertFalse($handler->handle($this->message));
    }

    public function testChildProcessThatExitsEarlyWithErrorReturnsFalseFromParent()
    {
        $handler = $this->createHandler(function () {
            // we can't throw here because php unit complains. Instead just call
            // `exit` with 255 to simulate exiting early.
            exit(255);
        });

        $this->assertFalse($handler->handle($this->message));
    }

    protected function setUp()
    {
        $this->message = new SimpleMessage(self::NAME);
    }

    private function createHandler(callable $callback)
    {
        return new PcntlForkingHandler(new CallableHandler($callback));
    }
}
