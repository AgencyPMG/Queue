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

/**
 * A simple must stop exception. Uses in tests.
 *
 * @since   2.0
 */
final class SimpleMustStop extends \RuntimeException implements MustStop
{
    // noop
}
