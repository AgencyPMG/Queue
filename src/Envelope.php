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
 * Envelopes wrap up messages and retry counts. End users never see Envelope
 * implementations.
 *
 * Envelope implementations are closely tied to their drivers.
 *
 * @since   2.0
 */
interface Envelope
{
    /**
     * Get the number of times the message has been attempted.
     *
     * @return  int
     */
    public function attempts();

    /**
     * Get the wrapped message.
     *
     * @return  Message
     */
    public function unwrap();

    /**
     * Returns a new envelop with all the same attributes but an incremented
     * attempt count.
     *
     * @return  Envelop
     */
    public function retry();
}
