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
 * Envelops wrap up messages and retry counts. End users never see Envelop
 * implementations. This is an interface because drivers may have specific
 * needs for thier own evelops, but the queue and retry strategies only care
 * about a few things.
 *
 * This is very much a "header" interface.
 *
 * @since   2.0
 */
interface Envelop
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
}
