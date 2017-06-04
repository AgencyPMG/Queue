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
 * Thrown by the `PcntlForkingHandler` when a call to `pcntl_fork` fails. This is
 * a `MustStop` exception that tells the consumer to unsuccessfully exit. Since a
 * failure to fork likely means some sort of resource exhausting the exit should
 * clean things up and hopefully do better the next time.
 *
 * @since 3.1.0
 */
final class CouldNotFork extends \RuntimeException implements MustStop
{
    public static function fromLastError()
    {
        $err = error_get_last();
        return new self(sprintf(
            'Could not fork child process to execute message: %s',
            isset($err['message']) ? $err['message'] : 'Unknown error'
        ), 1);
    }
}
