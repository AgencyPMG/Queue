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

class CallableHandlerTest extends \PMG\Queue\UnitTestCase
{
    const NAME = 'TestMessage';

    private $message;

    public function testHandlerInvokesTheCallbackWithTheMessage()
    {
        $calledWith = null;
        $handler = new CallableHandler(function ($msg) use (&$calledWith) {
            $calledWith = $msg;
            return true;
        });

        $this->assertTrue($handler->handle($this->message));
        $this->assertSame($this->message, $calledWith);
    }

    public function testHandlerInvokesTheCallbackWithTheRprovidedOptions()
    {
        $calledWith = null;
        $handler = new CallableHandler(function ($msg, $options) use (&$calledWith) {
            $calledWith = $options;
            return true;
        });

        $this->assertTrue($handler->handle($this->message, ['one' => true]));
        $this->assertSame($calledWith, ['one' => true]);
    }

    protected function setUp()
    {
        $this->message = new SimpleMessage(self::NAME);
    }
}
