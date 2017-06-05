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
use PMG\Queue\Signer\SodiumCryptoAuth;

class SodiumCryptoAuthNativeSerializerTest extends SerializerIntegrationTestCase
{
    protected function createSerializer() : Serializer
    {
        return new NativeSerializer(new SodiumCryptoAuth(str_repeat('test', 8)));
    }
}
