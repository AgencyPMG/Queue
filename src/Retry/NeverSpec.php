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

namespace PMG\Queue\Retry;

use PMG\Queue\Envelope;
use PMG\Queue\RetrySpec;

/**
 * Never retry a message.
 *
 * @since   2.0
 */
final class NeverSpec implements RetrySpec
{
    /**
     * {@inheritdoc}
     */
    public function canRetry(Envelope $env)
    {
        return false;
    }
}
