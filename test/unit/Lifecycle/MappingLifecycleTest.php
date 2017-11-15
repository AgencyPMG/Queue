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

use PMG\Queue\SimpleMessage;
use PMG\Queue\Exception\InvalidArgumentException;

class MappingLifecycleTest extends LifecycleTestCase
{
    private $child, $fallback, $lifecycle;

    public static function badMappings() : array
    {
        return [
            ['one'],
            [1],
            [true],
            [null],
            [new \stdClass],
        ];
    }

    /**
     * @dataProvider badMappings
     */
    public function testLifecyclesCannotBeCreatedWithNonArrayishObjects($mapping)
    {
        $this->expectException(InvalidArgumentException::class);
        new MappingLifecycle($mapping);
    }

    public function validMappings() : array
    {
        return [
            [['one' => new NullLifecycle()]],
            [new \ArrayObject(['one' => new NullLifecycle()])],
        ];
    }

    /**
     * @dataProvider validMappings
     */
    public function testLifecycleCanBeCreatedFromArrayishObject($mapping)
    {
        $lc = new MappingLifecycle($mapping);

        $this->assertTrue($lc->has('one'));
        $this->assertFalse($lc->has('two'));
    }

    public static function methods() : array
    {
        return [
            ['starting'],
            ['completed'],
            ['failed', true],
            ['succeeded'],
        ];
    }

    /**
     * @dataProvider methods
     */
    public function testLifecycleCallsFallbackIfMessageIsNotInMapping(string $method, ...$additional)
    {
        $message = new SimpleMessage('noope');
        $this->child->expects($this->never())
            ->method($method);
        $this->fallback->expects($this->once())
            ->method($method)
            ->with($this->identicalTo($message), $this->isConsumer(), ...$additional);

        call_user_func([$this->lifecycle, $method], $message, $this->consumer, ...$additional);
    }

    /**
     * @dataProvider methods
     */
    public function testLifecycleCanBeCreatedWithoutAFallbackAndStillWorksWithUnmappedMessages(string $method, ...$additional)
    {
        $message = new SimpleMessage('noope');
        $this->child->expects($this->never())
            ->method($method);
        $lc = new MappingLifecycle([
            $this->message->getName() => $this->child,
        ]);
    
        call_user_func([$lc, $method], $message, $this->consumer, ...$additional);
    }

    /**
     * @dataProvider methods
     */
    public function testLifecycleCallsChildLifecycleIfMappingHasMessage(string $method, ...$additional)
    {
        $this->child->expects($this->once())
            ->method($method)
            ->with($this->isMessage(), $this->isConsumer(), ...$additional);

        call_user_func([$this->lifecycle, $method], $this->message, $this->consumer, ...$additional);
    }

    protected function setUp()
    {
        parent::setUp();
        $this->child = $this->mockLifecycle();
        $this->fallback = $this->mockLifecycle();
        $this->lifecycle = new MappingLifecycle([
            $this->message->getName() => $this->child,
        ], $this->fallback);
    }
}
