<?php
/**
 * This file is part of PMG\Queue
 *
 * Copyright (c) PMG <https://www.pmg.com>
 *
 * For full copyright information see the LICENSE file distributed
 * with this source code.
 *
 * @license     http://opensource.org/licenses/MIT MIT
 */

namespace PMG\Queue\Factory;

use PMG\Queue\QueueFactory;

/**
 * Wraps other queue factories and caches queue objects based on their names.
 *
 * @since    2.0
 */
final class CachingFactory implements QueueFactory
{
    private $cache = [];

    /**
     * @var QueueFactory
     */
    private $wrapped;

    public function __construct(QueueFactory $wrapped)
    {
        $this->wrapped = $wrapped;
    }

    /**
     * {@inheritdoc}
     */
    public function forName($name)
    {
        if (!isset($this->cache[$name])) {
            $this->cache[$name] = $this->wrapped->forName($name);
        }

        return $this->cache[$name];
    }
}
