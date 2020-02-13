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

@trigger_error(sprintf(
    'The "%s" trait is deprecated since pmg/queue 5.0, a message\'s FQCN as the message name is the default in 5.0',
    MessageTrait::class
), E_USER_DEPRECATED);

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
    public function getName() : string
    {
        return get_class($this);
    }
}
