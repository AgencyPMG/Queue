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
 * A marker interface for exceptions that instructs consumers to release the
 * message that causes the error.
 *
 * This is useful for async handlers which may cancel things and need a way to
 * tell the consumers that the exception thrown from `Promise::wait` was intentional
 * in some way.
 *
 * @since 4.0
 */
interface ShouldReleaseMessage extends QueueException
{
    // noop
}
