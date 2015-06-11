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
use PMG\Queue\RetrySpec;
use PMG\Queue\Retry\LimitedSpec;

/**
 * Create new memory queues from their names.
 *
 * @since   2.0
 */
final class MemoryFactory implements QueueFactory
{
    /**
     * @var RetrySpec|null
     */
    private $retries;

    public function __construct(RetrySpec $retries=null)
    {
        $this->retries = $retries ?: new LimitedSpec();
    }

    /**
     * {@inheritdoc}
     */
    public function forName($name)
    {
        return new MemoryQueue($this->retries);
    }
}
