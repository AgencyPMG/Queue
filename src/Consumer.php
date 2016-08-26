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

/**
 * Consumer's pull messages out of the queue and execute them.
 *
 * @since   2.0
 * @api
 */
interface Consumer
{
    const EXIT_SUCCESS = 0;
    const EXIT_ERROR = 2;

    /**
     * Run the consumer for a given queue. This will block.
     *
     * @param   string $queueName The queue from which the jobs will be consumed.
     * @return  int The exit code to be used for the consumer.
     */
    public function run($queueName);

    /**
     * Consume a single job from the given queue. This will block until the
     * job is competed then return. Implementations of this method MUST be
     * safe to run in a loop.
     *
     * @param   string $queueName The queue from which jobs will be consumed.
     * @throws  Exception\MustStop if the executor or handler throws a must
     *          stop execption indicating a graceful stop is necessary
     * @throws  Exception\DriverError|Exception if anything goes wrong with the
     *          underlying driver itself.
     * @return  boolean|null True if the a job was execute successfully. Null if
     *          no job was executed. See the logs.
     */
    public function once($queueName);

    /**
     * Gracefully stop the consumer with the given exit code.
     *
     * @param int $code The exit code passed to `exit`. If null `EXIT_SUCCESS` is used.
     * @return void
     */
    public function stop($code=null);
}
