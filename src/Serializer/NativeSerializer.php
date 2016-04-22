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
    /**
     * Only applicable to PHP 7+. This is a set of allowed classes passed
     * to the second argument of `unserialize`.
     *
     * @var string[]
     */
    private $allowedClasses;

    public function __construct(array $allowedClasses=null)
    {
        if ($allowedClasses && !self::isPhp7()) {
            throw new \RuntimeException(sprintf(
                '$allowedClasses in %s only worked on PHP 7.0+, you are using PHP %s.',
                __METHOD__,
                PHP_VERSION
            ));
        }

        $this->allowedClasses = $allowedClasses;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize(Envelope $env)
    {
        return base64_encode(serialize($env));
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($message)
    {
        $m = $this->doUnserialize(base64_decode($message));
        if (!$m instanceof Envelope) {
            throw new SerializationError(sprintf(
                'Expected an instance of "%s" got "%s"',
                Envelope::class,
                is_object($m) ? get_class($m) : gettype($m)
            ));
        }

        return $m;
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
        $m = self::isPhp7() && $this->allowedClasses ? @unserialize($str, [
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

    private static function isPhp7()
    {
        return PHP_VERSION_ID >= 70000;
    }
}
