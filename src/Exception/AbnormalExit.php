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
 * Thrown by the `Pcntl` utility when a child process does not have a 
 * normal exit. When this happens it's treated as a message handling failure,
 * just like an unsuccessful exit would be.
 *
 * @since 3.2.0
 */
final class AbnormalExit extends \RuntimeException implements QueueException
{
    public static function fromWaitStatus($status)
    {
        if (pcntl_wifstopped($status)) {
            return new self(sprintf(
                'Child process was stopped with %s signal',
                pcntl_wstopsig($status)
            ));
        }

        if (pcntl_wifsignaled($status)) {
            return new self(sprintf(
                'Child process was terminated with %s signal',
                pcntl_wtermsig($status)
            ));
        }

        return new self(sprintf(
            'Child process exited abnormally (wait status: %s)',
            $status
        ));
    }
}
