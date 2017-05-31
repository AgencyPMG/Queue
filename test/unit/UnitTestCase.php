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

namespace PMG\Queue;

abstract class UnitTestCase extends \PHPUnit\Framework\TestCase
{
    protected function skipIfPhp7()
    {
        if (self::isPhp7()) {
            $this->markTestSkipped(sprintf('PHP < 7.X is required, have %s', PHP_VERSION));
        }
    }

    protected function skipIfPhp5()
    {
        if (!self::isPhp7()) {
            $this->markTestSkipped(sprintf('PHP 7.X is required, have %s', PHP_VERSION));
        }
    }

    protected static function isPhp7()
    {
        return PHP_VERSION_ID >= 70000;
    }
}
