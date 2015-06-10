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

use PMG\Queue\Message;
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
    public function serialize(Message $message)
    {
        return serialize($message);
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($message)
    {
        $m = @unserialize($message);
        if (false === $m) {
            $err = error_get_last();
            throw new SerializationError(sprintf(
                'Error unserializing message: %s', 
                isset($err['message']) ? $err['message'] : 'unknown error'
            ));
        }

        if (!$m instanceof Message) {
            throw new SerializationError(sprintf(
                'Expected an instance of "%s" got "%s"',
                Message::class,
                is_object($m) ? get_class($m) : gettype($m)
            ));
        }

        return $m;
    }
}
