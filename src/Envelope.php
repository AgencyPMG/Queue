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
 * Envelopes wrap up messages and retry counts. End users never see Envelope
 * implementations.
 *
 * Envelope implementations are closely tied to their drivers.
 *
 * @since   2.0
 */
interface Envelope
{
    const NO_DELAY = 0;

    /**
     * Get the number of times the message has been attempted.
     *
     * @return  int
     */
    public function attempts() : int;

    /**
     * Get the number of seconds which the message should be delayed.
     *
     * @return int
     */
    public function delay() : int;

    /**
     * Get the wrapped message.
     *
     * @return object the actual message
     */
    public function unwrap() : object;

    /**
     * Returns a new envelop with all the same attributes but an incremented
     * attempt count.
     *
     * @param $delay The amount number of seconds the message should be delayed before retrying
     * @return  Envelop
     */
    public function retry(int $delay=0) : Envelope;
}
