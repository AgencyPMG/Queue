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

namespace PMG\Queue\Serializer;

use PMG\Queue\Envelope;
use PMG\Queue\DefaultEnvelope;
use PMG\Queue\SimpleMessage;
use PMG\Queue\Exception\SerializationError;
use PMG\Queue\Signer\HmacSha256;

class HmacSha256NativeSerializerTest extends SerializerIntegrationTestCase
{
    const KEY = 'SuperSecretKey';

    public static function notStrings()
    {
        return [
            [['an array']],
            [new \stdClass],
            [1],
            [1.0],
            [null],
            [false],
        ];
    }

    /**
     * @group regression
     * @dataProvider notStrings
     */
    public function testSerializersCannotBeCreatedWithANonStringKey($key)
    {
        $this->expectException(\TypeError::class);

        NativeSerializer::fromSigningKey($key);
    }

    public function testSerializersCannotBeCreatedWithEmptyKeys()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('$key cannot be empty');

        NativeSerializer::fromSigningKey('');
    }

    /**
     * @group regresion
     */
    public function testMessagesSerializedInVersion2xCanStillBeUnserialized()
    {
        $oldMessage = 'bbb07fc3841b8114978c60bddcf61d3f0d167887250696a7974502f8ce42d136|TzoyNToiUE1HXFF1ZXVlXERlZmF1bHRFbnZlbG9wZSI6Mjp7czoxMDoiACoAbWVzc2FnZSI7TzoyMzoiUE1HXFF1ZXVlXFNpbXBsZU1lc3NhZ2UiOjI6e3M6Mjk6IgBQTUdcUXVldWVcU2ltcGxlTWVzc2FnZQBuYW1lIjtzOjE6InQiO3M6MzI6IgBQTUdcUXVldWVcU2ltcGxlTWVzc2FnZQBwYXlsb2FkIjtOO31zOjExOiIAKgBhdHRlbXB0cyI7aTowO30=';

        $env = $this->serializer->unserialize($oldMessage);

        $this->assertEquals($this->env, $env);
    }

    protected function createSerializer() : Serializer
    {
        return NativeSerializer::fromSigningKey(self::KEY);
    }
}
