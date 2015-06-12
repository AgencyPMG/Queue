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


/**
 * Thrown when an envelope for a driver isn't of the expected type.
 *
 * @since   2.0
 */
final class InvalidEnvelope extends \InvalidArgumentException implements DriverError
{
    // noop
}
