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

namespace PMG\Queue;

/**
 * Consumer's pull messages out of the queue and execute them.
 *
 * @since   2.0
 * @api
 */
interface Consumer
{
    /**
     * Run the consumer for a given queue. This will block.
     *
     * @param   string $queueName The queue from which the jobs will be consumed.
     * @return  void
     */
    public function run($queueName);

    /**
     * Consume a single job from the given queue. This will block until the
     * job is competed then return.
     *
     * @param   string $queueName The queue from which jobs will be consumed.
     * @throws  Exception if anything goes wrong
     * @return  void
     */
    public function once($queueName);
}
