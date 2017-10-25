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
 * Thrown by the `PcntlForkingHandler` when the child process exits unsuccessfully.
 *
 * @since 4.0
 */
final class ForkedProcessFailed extends \RuntimeException implements QueueException
{
    public static function withExitCode(int $exitCode) : self
    {
        return new self(sprintf('exit code %d', $exitCode), $exitCode);
    }
}
