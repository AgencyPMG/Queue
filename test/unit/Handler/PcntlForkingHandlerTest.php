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
use PMG\Queue\Exception\ForkedProcessCancelled;
use PMG\Queue\Exception\ForkedProcessFailed;
use PMG\Queue\Handler\Pcntl\Pcntl;

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

        $promise = $handler->handle($this->message);

        $this->assertTrue($promise->wait());
    }

    public function testChildProcessWithFailedResultCausesErrorInParent()
    {
        $this->expectException(ForkedProcessFailed::class);
        $this->expectExceptionMessage('exit code 1');
        $handler = $this->createHandler(function () {
            return false;
        });

        $handler->handle($this->message)->wait();
    }

    public function testChildProcessThatExitsEarlyWithErrorReturnsFalseFromParent()
    {
        $this->expectException(ForkedProcessFailed::class);
        $handler = $this->createHandler(function () {
            // we can't throw here because php unit complains. Instead just call
            // `exit` with 255 to simulate exiting early.
            exit(255);
        });

        $handler->handle($this->message)->wait();
    }

    public function testChildProcessThatThrowsAnExceptionExitsUnsuccessfully()
    {
        $this->expectException(ForkedProcessFailed::class);
        $handler = $this->createHandler(function () {
            throw new \Exception('oh noz');
        });

        $handler->handle($this->message)->wait();
    }

    public function testChildProcessWithErrorExitsUnsuccessfully()
    {
        $this->expectException(ForkedProcessFailed::class);
        $handler = $this->createHandler(function () {
            throw new \Error('oh noz');
        });

        $handler->handle($this->message)->wait();
    }

    public function testChildProcessIsPassedTheOptionsFromTheHandler()
    {
        $handler = $this->createHandler(function ($msg, $options) {
            // will cause an unsuccessful exit if it fails.
            $this->assertEquals($options, ['one' => true]);
            return true;
        });

        $promise = $handler->handle($this->message, ['one' => true]);

        $this->assertTrue($promise->wait());
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

    /**
     * @group slow
     */
    public function testHandlerPromisesCanBeCancelled()
    {
        $this->expectException(ForkedProcessCancelled::class);
        $handler = $this->createHandler(function () {
            sleep(10000);
            $this->assertTrue(false, 'causes a different exit exception');
        });

        $promise = $handler->handle($this->message);
        $promise->cancel();

        $promise->wait();
    }

    /**
     * @requires function pcntl_async_signals
     * @group slow
     */
    public function testHandlersWaitingThatAreCancelledAsynchronouslyFail()
    {
        $this->expectException(ForkedProcessCancelled::class);
        $handler = $this->createHandler(function () {
            sleep(10000);
            $this->assertTrue(false, 'causes a different exit exception');
        });
        $promise = $handler->handle($this->message);

        $previous = pcntl_async_signals(true);
        pcntl_signal(SIGALRM, function () use ($promise) {
            $promise->cancel();
        });
        pcntl_alarm(3);
        try {
            $promise->wait();
        } finally {
            pcntl_async_signals($previous);
            pcntl_signal(SIGALRM, SIG_DFL);
        }
    }

    protected function setUp() : void
    {
        $this->message = new SimpleMessage(self::NAME);
    }

    private function createHandler(callable $callback, Pcntl $pcntl=null)
    {
        return new PcntlForkingHandler(new CallableHandler($callback), $pcntl);
    }
}
