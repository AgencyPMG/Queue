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

namespace PMG\Queue\Signer;

/**
 * Sign or verify messages. This is used in conjuction with `NativeSerializer`
 * for the most part.
 *
 * @since 4.0
 */
interface Signer
{
    /**
     * Sign a message.
     *
     * @param $message The message to signe
     * @return a MAC that can be be associated with the message however the 
     *         caller sees fit.
     */
    public function sign(string $message) : string;

    /**
     * Verify the message signature.
     *
     * @param $signed The signed message
     * @return True if the message signature is valid.
     */
    public function verify(string $mac, string $message) : bool;
}
