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

namespace PMG\Queue\Handler;

use PMG\Queue\SimpleMessage;
use PMG\Queue\Exception\CouldNotFork;

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

    public function testChildProcessThatThrowsAnExceptionExitsUnsuccessfully()
    {
        $handler = $this->createHandler(function () {
            throw new \Exception('oh noz');
        });

        $this->assertFalse($handler->handle($this->message));
    }

    public function testChildProcessWithErrorExitsUnsuccessfully()
    {
        $handler = $this->createHandler(function () {
            throw new \Error('oh noz');
        });

        $this->assertFalse($handler->handle($this->message));
    }

    public function testChildProcessIsPassedTheOptionsFromTheHandler()
    {
        $handler = $this->createHandler(function ($msg, $options) {
            // will cause an unsuccessful exit if it fails.
            $this->assertEquals($options, ['one' => true]);
            return true;
        });

        $this->assertTrue($handler->handle($this->message, ['one' => true]));
    }

    public function testHandlerErrorsIfAChildProcessCannotFork()
    {
        $this->expectException(CouldNotFork::class);
        $pcntl = $this->createMock(Pcntl::class);
        $handler = $this->createHandler(function () {
            // ignored
        }, $pcntl);
        $pcntl->expects($this->once())
            ->method('fork')
            ->willReturn(-1);

        $handler->handle($this->message);
    }

    protected function setUp()
    {
        $this->message = new SimpleMessage(self::NAME);
    }

    private function createHandler(callable $callback, Pcntl $pcntl=null)
    {
        return new PcntlForkingHandler(new CallableHandler($callback), $pcntl);
    }
}
