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

namespace PMG\Queue\Exception;

use PMG\Queue\QueueException;

/**
 * A marker interface for exceptions thrown to indicate the consumer must exit
 * when it's running.
 *
 * For example, if persistent driver has a socket connection that fails, that
 * probably means the consumer should exit.
 *
 * @since   2.0
 */
interface MustStop extends QueueException
{
    // noop
}
