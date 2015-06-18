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
 * Serializer's turn messages into strings that can be sent into Queues and
 * deserialize stringy messages from queues back into objects.
 *
 * @since   2.0
 */
interface Serializer
{
    /**
     * Serialize a message into a string for sending into a queue.
     *
     * @param   $env The message envelope to serialize
     * @throws  SerializationError if the message cannot be serialized
     * @return  string A base 64 encoded string of the serialized envelope
     */
    public function serialize(Envelope $env);

    /**
     * Deserialize a string form the queue into a message object.
     *
     * @param   string $message a base 64 encoded string of the serialed envelope
     * @throws  SerializationError if something goes wrong during unserialization.
     * @return  Envelope
     */
    public function unserialize($message);
}
