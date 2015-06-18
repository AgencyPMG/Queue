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

namespace PMG\Queue\Exception;

use PMG\Queue\QueueException;

/**
 * A marker interface for exceptions thrown to indicate the consumer must exit
 * when it's running.
 *
 * These are a way to gracefully stop a queue. For example, if you've updated
 * code, putting a job in the queue that throws a MustStop would be a way to
 * stop the queue and have its process manager automatically restart it.
 *
 * @since   2.0
 */
interface MustStop extends QueueException
{
    // noop
}
