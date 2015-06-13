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

namespace PMG\Queue\Router;

use PMG\Queue\Message;
use PMG\Queue\Exception\InvalidArgumentException;

/**
 * A router implementation that maps message names to queue names via
 * an array or `ArrayAccess` implementation.
 *
 * @since   2.0
 */
final class MappingRouter implements \PMG\Queue\Router
{
    /**
     * The map of class name => queue name values.
     *
     * @var array
     */
    private $map;

    public function __construct($map)
    {
        if (!is_array($map) && !$map instanceof \ArrayAccess) {
            throw new InvalidArgumentException(sprintf(
                '%s expects $map must be an array or implementation of ArrayAccess, got "%s"',
                __METHOD__,
                is_object($map) ? get_class($map) : gettype($map)
            ));
        }

        $this->map = $map;
    }

    /**
     * {@inheritdoc}
     */
    public function queueFor(Message $message)
    {
        $name = $message->getName();
        return isset($this->map[$name]) ? $this->map[$name] : null;
    }
}
