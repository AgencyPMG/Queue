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

class HmacSha256Test extends SignerTestCase
{
    public function testSignerCannotBeCreatedWithEmptyKey()
    {
        $this->expectException(InvalidArgumentException::class);
        new HmacSha256('');
    }

    protected function createSigner()
    {
        return new HmacSha256('superSecretShhhh');
    }
}
