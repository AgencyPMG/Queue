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

namespace PMG\Queue\Lifecycle;

use PMG\Queue\MessageLifecycle;
use PMG\Queue\Consumer;
use PMG\Queue\SimpleMessage;

abstract class LifecycleTestCase extends \PMG\Queue\UnitTestCase
{
    protected $consumer, $message;

    protected function setUp() : void
    {
        $this->consumer = $this->createMock(Consumer::class);
        $this->message = new SimpleMessage('example');
    }

    protected function mockLifecycle() : MessageLifecycle
    {
        return $this->createMock(MessageLifecycle::class);
    }

    protected function isConsumer()
    {
        return $this->identicalTo($this->consumer);
    }

    protected function isMessage()
    {
        return $this->identicalTo($this->message);
    }
}
