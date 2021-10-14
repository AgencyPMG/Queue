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

use PMG\Queue\MessageNames;
use PMG\Queue\SimpleMessage;
use PMG\Queue\Exception\InvalidArgumentException;

/**
 * @group lifecycles
 */
class MappingLifecycleTest extends LifecycleTestCase
{
    use MessageNames;

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
            ['retrying'],
            ['failed'],
            ['succeeded'],
        ];
    }

    /**
     * @dataProvider methods
     */
    public function testLifecycleCallsFallbackIfMessageIsNotInMapping(string $method)
    {
        $message = new SimpleMessage('noope');
        $this->child->expects($this->never())
            ->method($method);
        $this->fallback->expects($this->once())
            ->method($method)
            ->with($this->identicalTo($message), $this->isConsumer());

        call_user_func([$this->lifecycle, $method], $message, $this->consumer);
    }

    /**
     * @dataProvider methods
     */
    public function testLifecycleCanBeCreatedWithoutAFallbackAndStillWorksWithUnmappedMessages(string $method)
    {
        $message = new SimpleMessage('noope');
        $this->child->expects($this->never())
            ->method($method);
        $lc = new MappingLifecycle([
            self::nameOf($this->message) => $this->child,
        ]);
    
        call_user_func([$lc, $method], $message, $this->consumer);
    }

    /**
     * @dataProvider methods
     */
    public function testLifecycleCallsChildLifecycleIfMappingHasMessage(string $method)
    {
        $this->child->expects($this->once())
            ->method($method)
            ->with($this->isMessage(), $this->isConsumer());

        call_user_func([$this->lifecycle, $method], $this->message, $this->consumer);
    }

    protected function setUp() : void
    {
        parent::setUp();
        $this->child = $this->mockLifecycle();
        $this->fallback = $this->mockLifecycle();
        $this->lifecycle = new MappingLifecycle([
            self::nameOf($this->message) => $this->child,
        ], $this->fallback);
    }
}
