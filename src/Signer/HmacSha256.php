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

use PMG\Queue\Exception\InvalidArgumentException;

/**
 * Uses hash_hmac and sha256 to sign/validate messages.
 *
 * @since 4.0
 */
final class HmacSha256 implements Signer
{
    const ALGO = 'sha256';

    /**
     * @var string
     */
    private $key;

    /**
     * Constructor.
     *
     * @param $key The key with which messages will be signed.
     */
    public function __construct(string $key)
    {
        InvalidArgumentException::assertNotEmpty($key, '$key cannot be empty!');
        $this->key = $key;
    }

    /**
     * {@inheritdoc}
     */
    public function sign(string $message) : string
    {
        return hash_hmac(self::ALGO, $message, $this->key, false);
    }

    /**
     * {@inheritdoc}
     */
    public function verify(string $mac, string $message) : bool
    {
        return hash_equals($this->sign($message), $mac);
    }
}
