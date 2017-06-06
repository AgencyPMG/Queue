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

final class SodiumCryptoAuth implements Signer
{
    /**
     * @var string
     */
    private $key;

    /**
     * Constructor. This tries to sign a dummy message immediately to validate
     * that the key okay for libsodium.
     *
     * @param $key The key with which messages will be signed.
     */
    public function __construct(string $key)
    {
        // @codeCoverageIgnoreStart
        if (!function_exists('sodium_crypto_auth')) {
            throw new \RuntimeException(sprintf(
                'sodium_* functions are not available, cannot use %s',
                __CLASS__
            ));
        }
        // @codeCoverageIgnoreEnd
        InvalidArgumentException::assertNotEmpty($key, '$key cannot be empty!');
        $this->key = $key;
        try {
            $this->sign('test message, please ignore');
        } catch (\Throwable $e) {
            throw new InvalidArgumentException($e->getMessage(), 0, $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function sign(string $message) : string
    {
        return sodium_crypto_auth($message, $this->key);
    }

    /**
     * {@inheritdoc}
     */
    public function verify(string $mac, string $message) : bool
    {
        return sodium_crypto_auth_verify($mac, $message, $this->key);
    }
}
