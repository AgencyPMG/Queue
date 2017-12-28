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

class MessageNamesTest extends UnitTestCase
{
    use MessageNames;

    public function testNameOfReturnsTheValueOfMessageGetName()
    {
        $msg = $this->createMock(Message::class);
        $msg->expects($this->once())
            ->method('getName')
            ->willReturn('test');

        $this->assertSame('test', self::nameOf($msg));
    }
}
