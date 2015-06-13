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
 * Extend `InvalidArgumentException` so we can marker interface it.
 *
 * @since   2.0
 */
final class InvalidArgumentException extends \InvalidArgumentException implements QueueException
{

}
