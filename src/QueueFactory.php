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

namespace PMG\Queue;

/**
 * Creates queue objects from names.
 *
 * @since   2.0
 */
interface QueueFactory
{
    /**
     * Create a new Queue from a name
     *
     * @param   string $name The queue's name
     * @return  Queue
     */
    public function forName($name);
}
