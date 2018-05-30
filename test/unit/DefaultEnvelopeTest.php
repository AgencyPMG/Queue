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

use PMG\Queue\Exception\InvalidArgumentException as InvalidArg;

class DefaultEnvelopeTest extends UnitTestCase
{
    private $message;

    public function testEnvelopeCannotBeCreatedWithAttempsLessThanZero()
    {
        $this->expectException(InvalidArg::class);
        new DefaultEnvelope($this->message, -1);
    }

    public function testEnvelopeCannotBeCreatedWithDelayLessThanZero()
    {
        $this->expectException(InvalidArg::class);
        new DefaultEnvelope($this->message, 0, -1);
    }

    public function testRetrySetsTheDelayProvidedOnTheNewEnvelope()
    {
        $e = new DefaultEnvelope($this->message);

        $retry = $e->retry(10);

        $this->assertEquals(10, $retry->delay());
    }

    public function testMessageCannotBeRetriedWithADelayLessThanZero()
    {
        $this->expectException(InvalidArg::class);
        $e = new DefaultEnvelope($this->message);

        $e->retry(-1);
    }

    protected function setUp()
    {
        $this->message = new SimpleMessage('test');
    }
}
