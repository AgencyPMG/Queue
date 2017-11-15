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

class DelegatingLifecycleTest extends LifecycleTestCase
{
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
    public function testLifecycleCallsChildLifecyclesWithProvidedArguments(string $method, ...$additional)
    {
        $lc = $this->mockLifecycle();
        $lc->expects($this->once())
            ->method($method)
            ->with($this->isMessage(), $this->isConsumer(), ...$additional);

        $dl = new DelegatingLifecycle($lc);

        call_user_func([$dl, $method], $this->message, $this->consumer, ...$additional);
    }

    public function testDelegatingLifecyclesCanBeCreatedFromAnArray()
    {
        $dl = DelegatingLifecycle::fromArray([
            $this->mockLifecycle(),
            $this->mockLifecycle(),
            $this->mockLifecycle(),
        ]);

        $this->assertCount(3, $dl, 'should have three child lifecycles');
    }
}
