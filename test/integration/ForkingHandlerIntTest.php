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

namespace PMG\Queue;

/**
 * @requires function pcntl_async_signals
 */
class ForkingHandlerIntTest extends IntegrationTestCase
{
    private $driver, $producer;

    /**
     * @group slow
     */
    public function testMessagesCanBeCancelledWhileInProgressFromConsumerStop()
    {
        $this->producer->send(new SimpleMessage('TestMessage'));
        $consumer = $this->createConsumer(function () {
            sleep(100);
        });
        pcntl_signal(SIGALRM, function () use ($consumer) {
            $consumer->stop(1);
        });
        pcntl_alarm(5);

        $exitCode = $consumer->run('q');

        $this->assertSame(1, $exitCode);
    }

    protected function setUp() : void
    {
        pcntl_signal(SIGALRM, SIG_DFL);
        pcntl_async_signals(true);
        $this->driver = new Driver\MemoryDriver();

        $this->producer = new DefaultProducer(
            $this->driver,
            new Router\SimpleRouter('q')
        );
    }

    private function createConsumer(callable $handler) : DefaultConsumer
    {
        return new DefaultConsumer(
            $this->driver,
            new Handler\PcntlForkingHandler(new Handler\CallableHandler($handler)),
            new Retry\NeverSpec()
        );
    }
}
