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
 * ABC for messages, implements `getName` as the class name.
 *
 * @since   2015-06-11
 */
trait MessageTrait
{
    /**
     * @see Message::getName
     */
    public function getName()
    {
        return get_class($this);
    }
}
