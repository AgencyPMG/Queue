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

class SodiumCryptoAuthTest extends SignerTestCase
{
    public function testSignerCannotBeCreatedWithEmptyKey()
    {
        $this->expectException(InvalidArgumentException::class);
        new SodiumCryptoAuth('');
    }

    public static function invalidKeys()
    {
        return [
            'too short' => ['test'],
            'too long' => [str_repeat('test', 100)],
        ];
    }

    /**
     * @dataProvider invalidKeys
     */
    public function testSignerCannotBeCreatedWithInvalidKey(string $key)
    {
        $this->expectException(InvalidArgumentException::class);
        new SodiumCryptoAuth($key);
    }

    protected function createSigner()
    {
        return new SodiumCryptoAuth(str_repeat('test', 8));
    }
}
