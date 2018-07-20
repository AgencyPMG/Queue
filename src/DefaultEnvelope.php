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

namespace PMG\Queue;

use PMG\Queue\Exception\InvalidArgumentException as InvalidArg;

/**
 * Default implementation of the `Envelop` with no extras.
 *
 * @since   2.0
 */
class DefaultEnvelope implements Envelope
{
    /**
     * @var Message
     */
    protected $message;

    /**
     * @var int
     */
    protected $attempts;

    /**
     * @var int
     */
    private $delay;

    public function __construct(Message $message, int $attempts=0, int $delay=Envelope::NO_DELAY)
    {
        InvalidArg::assert($attempts >= 0, '$attempts must be >= 0');
        $this->message = $message;
        $this->attempts = $attempts;
        $this->setDelay($delay);
    }

    /**
     * {@inheritdoc}
     */
    public function attempts() : int
    {
        return $this->attempts;
    }

    /**
     * {@inheritdoc}
     */
    public function delay() : int
    {
        return $this->delay;
    }

    /**
     * {@inheritdoc}
     */
    public function unwrap() : Message
    {
        return $this->message;
    }

    /**
     * {@inheritdoc}
     */
    public function retry(int $delay=0) : Envelope
    {
        $new = clone $this;
        $new->attempts++;
        $new->setDelay($delay);

        return $new;
    }

    /**
     * checks to make sure the `$message` property is really a message. Serializers
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

    protected function setDelay(int $delay) : void
    {
        InvalidArg::assert($delay >= 0, '$delay must be >= 0');
        $this->delay = $delay;
    }
}
