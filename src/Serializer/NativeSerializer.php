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

namespace PMG\Queue\Serializer;

use PMG\Queue\Envelope;
use PMG\Queue\Exception\SerializationError;

/**
 * A serializer implemtnation that uses PHP's native serialize and unserialize.
 *
 * @since   2.0
 */
final class NativeSerializer implements Serializer
{
    const HMAC_ALGO = 'sha256';

    /**
     * Only applicable to PHP 7+. This is a set of allowed classes passed
     * to the second argument of `unserialize`.
     *
     * @var string[]
     */
    private $allowedClasses;

    /**
     * @var string
     */
    private $key;

    public function __construct(string $key, array $allowedClasses=null)
    {
        if (empty($key)) {
            throw new \InvalidArgumentException('$key cannot be empty');
        }

        $this->key = $key;
        $this->allowedClasses = $allowedClasses;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize(Envelope $env)
    {
        $str = base64_encode(serialize($env));
        return sprintf('%s|%s', $this->hmac($str), $str);
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($message)
    {
        $env = $this->verifySignature($message);

        $m = $this->doUnserialize(base64_decode($env));
        if (!$m instanceof Envelope) {
            throw new SerializationError(sprintf(
                'Expected an instance of "%s" got "%s"',
                Envelope::class,
                is_object($m) ? get_class($m) : gettype($m)
            ));
        }

        return $m;
    }

    private function verifySignature($message)
    {
        if (substr_count($message, '|') !== 1) {
            throw new SerializationError('Data to unserialize does not have a signature');
        }

        list($sig, $env) = explode('|', $message, 2);
        if (!hash_equals($this->hmac($env), $sig)) {
            throw new SerializationError('Invalid HMAC Signature');
        }

        return $env;
    }

    /**
     * Small wrapper around `unserialize` so we can pass in `$allowedClasses`
     * if the PHP verison 7+
     *
     * @param string $str the string to unserialize
     * @return object|false
     */
    private function doUnserialize($str)
    {
        $m = $this->allowedClasses ? @unserialize($str, [
            'allowed_classes' => $this->allowedClasses,
        ]) : @unserialize($str);

        if (false === $m) {
            $err = error_get_last();
            throw new SerializationError(sprintf(
                'Error unserializing message: %s', 
                $err && isset($err['message']) ? $err['message'] : 'unknown error'
            ));
        }

        return $m;
    }

    private function hmac($data)
    {
        return hash_hmac(self::HMAC_ALGO, $data, $this->key, false);
    }
}
