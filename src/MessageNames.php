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

trait MessageNames
{
    protected static function nameOf(object $message) : string
    {
        if ($message instanceof NamedMessage) {
            return $message->getName();
        }

        return get_class($message);
    }
}
