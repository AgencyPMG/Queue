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

namespace PMG\Queue\Exception;

use PMG\Queue\QueueException;

/**
 * Marker interface for errors from drivers. When this is thrown the queue system
 * assumes something is wrong with the driver itself and so every job should be
 * retried.
 *
 * @since   2.0
 */
interface DriverError extends QueueException
{

}
