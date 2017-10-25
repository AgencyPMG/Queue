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

namespace PMG\Queue\Handler\Pcntl;

/**
 * A result object returned from Pcntl::wait.
 *
 * This encapsulates the "was the process successful?" question as well as provides
 * a way to access the exit code of the process.
 *
 * @since 4.1
 * @internal
 */
final class WaitResult
{
    private $exitCode;

    public function __construct(int $exitCode)
    {
        $this->exitCode = $exitCode;
    }

    public function successful() : bool
    {
        return 0 === $this->exitCode;
    }

    public function getExitCode() : int
    {
        return $this->exitCode;
    }
}
