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
 * Thrown when a handler for a job is invalid (not a callable).
 *
 * @since   2.0
 */
final class InvalidHandler extends \UnexpectedValueException implements QueueException
{

}
