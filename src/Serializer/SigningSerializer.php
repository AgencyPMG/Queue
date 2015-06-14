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

namespace PMG\Queue\Serializer;

use PMG\Queue\Envelope;
use PMG\Queue\Exception\SerializationError;

/**
 * A serializer decorator that signs outgoing messages with a hmac. Unserializing
 * data from the network is probably a bad idea, so verifying the integrity of it
 * via an HMAC can help mitigate that a bit.
 *
 * @since   2.0
 */
final class SigningSerializer implements Serializer
{
    const DEFAULT_ALGO = 'sha256';

    /**
     * @var Serializer
     */
    private $wrapped;

    /**
     * @var string
     */
    private $key;

    /**
     * @var string
     */
    private $algo;

    public function __construct(Serializer $wrapped, $key, $algo=null)
    {
        $this->wrapped = $wrapped;
        $this->key = $key;
        $this->algo = $algo ?: self::DEFAULT_ALGO;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize(Envelope $env)
    {
        $res = $this->wrapped->serialize($env);
        return sprintf('%s|%s', $this->hmac($res), $res);
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($data)
    {
        if (substr_count($data, '|') !== 1) {
            throw new SerializationError('Data to unserialize does not have a signature');
        }

        list($sig, $env) = explode('|', $data, 2);
        if ($this->hmac($env) !== $sig) {
            throw new SerializationError('HMAC signature does not match');
        }

        return $this->wrapped->unserialize($env);
    }

    private function hmac($data)
    {
        return hash_hmac($this->algo, $data, $this->key, false);
    }
}
