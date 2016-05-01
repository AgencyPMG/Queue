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

namespace PMG\Queue\Driver;

use PMG\Queue\Envelope;
use PMG\Queue\DefaultEnvelope;
use PMG\Queue\Serializer\Serializer;
use PMG\Queue\Serializer\NativeSerializer;

/**
 * Base class for drivers that deal with persistent backends. This provides
 * some utilities for serialization.
 *
 * @since   2.0
 */
abstract class AbstractPersistanceDriver implements \PMG\Queue\Driver
{
    /**
     * @var Serializer
     */
    private $serializer;

    public function __construct(Serializer $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * Returns a set of allowed classes for the serializer. This may not be used:
     * it depends on what serializer the end user decided on. The idea here
     * is that the envelope class names remain opaque to the user (because they
     * should not care about them: envelopes are internal to drivers and the queue
     * only).
     *
     * Example (with `NativeSerializer` and `PheanstalkDriver`):
     *
     *   $serializer = new NativeSerializer(array_merge([
     *      SomeMessage::class,
     *   ], PheanstalkDriver::allowedClasses()));
     *
     * @return string[]
     */
    public static function allowedClasses()
    {
        return [
            DefaultEnvelope::class,
        ];
    }

    protected function serialize(Envelope $env)
    {
        return $this->assureSerializer()->serialize($env);
    }

    protected function unserialize($data)
    {
        return $this->assureSerializer()->unserialize($data);
    }

    protected function assureSerializer()
    {
        if (!$this->serializer) {
            throw new \RuntimeException(sprintf(
                '%s does not have a serializer set, did you forget to call parent::__construct($serializer) in its constructor?',
                get_class($this)
            ));
        }

        return $this->serializer;
    }
}
