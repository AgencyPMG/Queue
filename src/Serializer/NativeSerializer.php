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
use PMG\Queue\Signer\Signer;
use PMG\Queue\Signer\HmacSha256;

/**
 * A serializer implemtnation that uses PHP's native serialize and unserialize.
 *
 * @since   2.0
 */
final class NativeSerializer implements Serializer
{
    /**
     * Only applicable to PHP 7+. This is a set of allowed classes passed
     * to the second argument of `unserialize`.
     *
     * @var string[]
     */
    private $allowedClasses;

    /**
     * @var Signer
     */
    private $signer;

    public function __construct(Signer $signer, array $allowedClasses=null)
    {
        $this->signer = $signer;
        $this->allowedClasses = $allowedClasses;
    }

    public static function fromSigningKey(string $key, array $allowedClasses=null)
    {
        return new self(new HmacSha256($key), $allowedClasses);
    }

    /**
     * {@inheritdoc}
     */
    public function serialize(Envelope $env)
    {
        $str = base64_encode(serialize($env));
        return sprintf('%s|%s', $this->signer->sign($str), $str);
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

    private function verifySignature(string $message) : string
    {
        if (substr_count($message, '|') !== 1) {
            throw new SerializationError('Data to unserialize does not have a signature');
        }

        list($sig, $env) = explode('|', $message, 2);
        if (!$this->signer->verify($sig, $env)) {
            throw new SerializationError('Invalid Message Signature');
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
}
