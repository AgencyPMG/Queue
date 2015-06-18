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
 * A message that returns the name given to it.
 *
 * @since   2.0
 */
final class SimpleMessage implements Message
{
    private $name;
    private $payload;

    public function __construct($name, $payload=null)
    {
        $this->name = $name;
        $this->payload = $payload;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function getPayload()
    {
        return $this->payload;
    }
}
