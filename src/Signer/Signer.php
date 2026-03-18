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
 * Sign or verify messages. This is used in conjunction with `NativeSerializer`
 * in most cases.
 *
 * @since 4.0
 */
interface Signer
{
    /**
     * Sign a message.
     *
     * @param $message The message to sign
     * @return string A MAC that can be associated with the message however the
     *         caller sees fit.
     */
    public function sign(string $message) : string;

    /**
     * Verify the message signature.
     *
     * @param $mac The MAC to verify
     * @param $message The message that was signed
     * @return bool True if the message signature is valid.
     */
    public function verify(string $mac, string $message) : bool;
}
