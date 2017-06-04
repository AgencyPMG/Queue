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

namespace PMG\Queue\Signer;

use PMG\Queue\Exception\InvalidArgumentException;

abstract class SignerTestCase extends \PMG\Queue\UnitTestCase
{
    protected $signer;

    public function testSignReturnsAMacString()
    {
        $mac = $this->signer->sign('hello, world');

        $this->assertInternalType('string', $mac);
        $this->assertNotEmpty($mac);
    }

    public function testVerifyReturnsTrueWhenTheMessageIsTheSame()
    {
        $mac = $this->signer->sign('hello, world');

        $result = $this->signer->verify($mac, 'hello, world');

        $this->assertTrue($result);
    }

    public function testVerifyReturnsFalseWhenTheMessageIsNotTheSame()
    {
        $mac = $this->signer->sign('hello, world');

        $result = $this->signer->verify($mac, 'goodbye, world');

        $this->assertFalse($result);
    }

    protected function setUp()
    {
        $this->signer = $this->createSigner();
    }

    abstract protected function createSigner();
}
