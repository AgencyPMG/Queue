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
     * @param   $message The message to serialize
     * @throws  SerializationError if the message cannot be serialized
     * @return  string
     */
    public function serialize(Message $message);

    /**
     * Deserialize a string form the queue into a message object.
     *
     * @param   string $message
     * @throws  SerializationError if something goes wrong during unserialization.
     * @return  Message
     */
    public function unserialize($message);
}
