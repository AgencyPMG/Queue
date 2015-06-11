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
use PMG\Queue\Queue\MemoryQueue;

/**
 * Create new memory queues from their names.
 *
 * @since   2.0
 */
final class MemoryFactory implements QueueFactory
{
    /**
     * {@inheritdoc}
     */
    public function forName($name)
    {
        return new MemoryQueue();
    }
}
