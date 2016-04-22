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

namespace PMG\Queue;

/**
 * Default implementation of the `Envelop` with no extras.
 *
 * @since   2.0
 */
class DefaultEnvelope implements Envelope
{
    protected $message;
    protected $attempts;

    public function __construct(Message $message, $attempts=0)
    {
        $this->message = $message;
        $this->attempts = $attempts;
    }

    /**
     * {@inheritdoc}
     */
    public function attempts()
    {
        return $this->attempts;
    }

    /**
     * {@inheritdoc}
     */
    public function unwrap()
    {
        return $this->message;
    }

    /**
     * {@inheritdoc}
     */
    public function retry()
    {
        $new = clone $this;
        $new->attempts++;

        return $new;
    }

    /**
     * checks to make sure the `$message` property is really a message. Serializes
     * may (optionally) whitelist classes. If we don't get a message back the 
     * envelope is kind of ****ed.
     *
     * @return void
     */
    public function __wakeup()
    {
        if (!$this->message instanceof Message) {
            throw new Exception\SerializationError(sprintf(
                '%s expected its message property to be unserialized with an instance of %s, got "%s"',
                __CLASS__,
                Message::class,
                get_class($this->message)
            ));
        }
    }
}
