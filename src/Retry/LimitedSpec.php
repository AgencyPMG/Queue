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
    private $retryDelay;

    public function __construct(int $maxAttempts=null, int $retryDelay=0)
    {
        if (null !== $maxAttempts && $maxAttempts < 1) {
            throw new \InvalidArgumentException(sprintf(
                '$maxAttempts must be a positive integer, got "%s"',
                $maxAttempts
            ));
        }

        if ($retryDelay < 0) {
            throw new \InvalidArgumentException(sprintf(
                '$retryDelay must be a positive integer, got "%s"',
                $retryDelay
            ));
        }

        $this->maxAttempts = $maxAttempts ?: self::DEFAULT_ATTEMPTS;
        $this->retryDelay = $retryDelay;
    }

    /**
     * {@inheritdoc}
     */
    public function canRetry(Envelope $env) : bool
    {
        return $env->attempts() < $this->maxAttempts;
    }

    /**
     * {@inheritdoc}
     */
    public function retryDelay(Envelope $env): int
    {
        return $this->retryDelay;
    }
}
