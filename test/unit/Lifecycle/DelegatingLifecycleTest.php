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

/**
 * @group lifecycles
 */
class DelegatingLifecycleTest extends LifecycleTestCase
{
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
    public function testLifecycleCallsChildLifecyclesWithProvidedArguments(string $method)
    {
        $lc = $this->mockLifecycle();
        $lc->expects($this->once())
            ->method($method)
            ->with($this->isMessage(), $this->isConsumer());

        $dl = new DelegatingLifecycle($lc);

        call_user_func([$dl, $method], $this->message, $this->consumer);
    }

    public function testDelegatingLifecyclesCanBeCreatedFromIterables()
    {
        $dl = DelegatingLifecycle::fromIterable((function () {
            foreach (range(1, 3) as $i) {
                yield $this->mockLifecycle();
            }
        })());

        $this->assertCount(3, $dl, 'should have three child lifecycles');
    }
}
