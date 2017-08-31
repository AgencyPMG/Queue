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

namespace PMG\Queue\Handler;

use PMG\Queue\Exception\AbnormalExit;

/**
 * While most of pcntl "happy" path stuff is handled by `PcntlForkingHandlerTest`
 * this one covers some of the less happy path stuff like abnormal exits, etc.
 *
 * @requires extension pcntl
 * @requires extension posix
 */
class PcntlTest extends \PMG\Queue\UnitTestCase
{
    private $pcntl;

    public function testStoppedProcessesThrowsAbnormalExit()
    {
        $this->expectException(AbnormalExit::class);
        $this->expectExceptionMessage('was stopped with');

        $pid = $this->fork();
        if ($pid) {
            posix_kill($pid, SIGSTOP);
            try {
                $this->pcntl->wait($pid);
            } finally {
                // continue, then kill the stopped process.
                $this->pcntl->signal($pid, SIGCONT);
                $this->pcntl->signal($pid, SIGTERM);
                pcntl_waitpid($pid, $status, WUNTRACED);
                $this->assertTrue(pcntl_wifsignaled($status));
            }
        } else {
            self::waitAndExit(5, 0);
        }
    }

    public function testInterruptedProcessesThrowAbnormalExit()
    {
        $this->expectException(AbnormalExit::class);
        $this->expectExceptionMessage('was terminated with');

        $pid = $this->fork();
        if ($pid) {
            $this->pcntl->signal($pid, SIGINT);
            $this->pcntl->wait($pid);
        } else {
            self::waitAndExit(5, 0);
        }
    }

    protected function setUp()
    {
        $this->pcntl = new Pcntl();
    }

    private function fork()
    {
        $pid = $this->pcntl->fork();
        if (-1 === $pid) {
            $this->markTestSkipped('Could not fork!');
        }

        return $pid;
    }

    private static function waitAndExit($time, $status)
    {
        sleep($time);
        exit($status);
    }
}
