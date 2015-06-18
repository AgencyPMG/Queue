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
        $m = @unserialize(base64_decode($message));
        if (false === $m) {
            $err = error_get_last();
            throw new SerializationError(sprintf(
                'Error unserializing message: %s', 
                $err && isset($err['message']) ? $err['message'] : 'unknown error'
            ));
        }

        if (!$m instanceof Envelope) {
            throw new SerializationError(sprintf(
                'Expected an instance of "%s" got "%s"',
                Envelope::class,
                is_object($m) ? get_class($m) : gettype($m)
            ));
        }

        return $m;
    }
}
