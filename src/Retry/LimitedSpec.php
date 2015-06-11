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
 * Retry a message a limited number of times.
 *
 * @since   2.0
 */
final class LimitedSpec implements RetrySpec
{
    const DEFAULT_ATTEMPTS = 5;

    private $maxAttempts;

    public function __construct($maxAttempts=null)
    {
        if (null !== $maxAttempts && $maxAttempts < 1) {
            throw new \InvalidArgumentException(sprintf(
                '$maxAttempts must be a positive integer, got "%s"',
                $maxAttempts
            ));
        }

        $this->maxAttempts = $maxAttempts ?: self::DEFAULT_ATTEMPTS;
    }

    /**
     * {@inheritdoc}
     */
    public function canRetry(Envelope $env)
    {
        return $env->attempts() < $this->maxAttempts;
    }
}
