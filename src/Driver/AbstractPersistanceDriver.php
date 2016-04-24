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

    public function __construct(Serializer $serializer=null)
    {
        $this->serializer = $serializer;
    }

    protected function serialize(Envelope $env)
    {
        return $this->getSerializer()->serialize($env);
    }

    protected function unserialize($data)
    {
        return $this->getSerializer()->unserialize($data);
    }

    protected function getSerializer()
    {
        if (!$this->serializer) {
            $this->serializer = new NativeSerializer();
        }

        return $this->serializer;
    }
}
