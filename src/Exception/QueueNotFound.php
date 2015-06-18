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
 * Thrown when a queue cannot be located for a message.
 *
 * @since   2.0
 */
final class QueueNotFound extends \RuntimeException implements QueueException
{
    // noop
}
