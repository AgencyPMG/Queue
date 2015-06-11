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

namespace PMG\Queue\Queue;

use PMG\Queue\Envelope;
use PMG\Queue\RetrySpec;
use PMG\Queue\Retry\LimitedSpec;

/**
 * ABC for queues -- provides the retry specification stuff.
 *
 * @since   2.0
 */
abstract class AbstractQueue implements \PMG\Queue\Queue
{
    /**
     * @var RetrySpec
     */
    private $retries;

    public function __construct(RetrySpec $retries=null)
    {
        $this->retries = $retries;
    }

    protected function canRetry(Envelope $env)
    {
        if (null === $this->retries) {
            $this->retries = new LimitedSpec();
        }

        return $this->retries->canRetry($env);
    }
}
