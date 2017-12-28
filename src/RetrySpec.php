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

namespace PMG\Queue;

/**
 * Some queue implementations use retry strategies to determine whether or
 * not a given message should be retried after failure.
 *
 * @since   2.0
 * @api
 */
interface RetrySpec
{
    /**
     * Given an envelop check whether or not it can be retried.
     *
     * @param   $env The envelop to check
     * @return  boolean True if the message should be retried.
     */
    public function canRetry(Envelope $env) : bool;

    /**
     * Get the number of seconds before an envelop can be retried.
     *
     * @since 5.0.0
     */
    public function retryDelay(Envelope $env): int;
}
