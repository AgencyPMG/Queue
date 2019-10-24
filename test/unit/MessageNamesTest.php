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

class _NamesTestMsg implements Message
{

}

class MessageNamesTest extends UnitTestCase
{
    use MessageNames;

    public function testNameOfReturnsTheValueOfMessageGetName()
    {
        $msg = $this->createMock(NamedMessage::class);
        $msg->expects($this->once())
            ->method('getName')
            ->willReturn('test');

        $this->assertSame('test', self::nameOf($msg));
    }

    public function testNameOfReturnsTheFullyQualifiedClassNameOfAMessageWhenNotNamed()
    {
        $name = self::nameOf(new _NamesTestMsg());

        $this->assertSame(_NamesTestMsg::class, $name);
    }

    public function testAnyObjectCanBePassedAndNameOfReturnsFqcn()
    {
        $name = self::nameOf($this);

        $this->assertSame(__CLASS__, $name);
    }
}
