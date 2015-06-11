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

/**
 * A router implementation that maps message class names to Queue names.
 *
 * @since   2.0
 */
final class SimpleRouter implements \PMG\Queue\Router
{
    /**
     * The map of class name => queue name values.
     *
     * @var array
     */
    private $map;

    public function __construct(array $map)
    {
        $this->map = $map;
    }

    /**
     * {@inheritdoc}
     */
    public function queueFor(Message $message)
    {
        $cls = get_class($message);
        return isset($this->map[$cls]) ? $this->map[$cls] : null;
    }
}
