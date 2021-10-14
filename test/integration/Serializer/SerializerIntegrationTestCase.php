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

/**
 * Covers the basic cases serializers. This exists so we can test they they
 * play nice with the various signers.
 */
abstract class SerializerIntegrationTestCase extends \PMG\Queue\IntegrationTestCase
{
    protected $serializer;

    public function testSerializeReturnsAStringThatCanBeUnserialized()
    {
        $s = $this->serializer->serialize($this->env);
        $this->assertIsString($s);

        $env = $this->serializer->unserialize($s);
        $this->assertEquals($this->env, $env);
    }

    public function testUnserializeErrorsWhenAnUnsignedStringIsGiven()
    {
        $this->expectException(SerializationError::class);
        $this->expectExceptionMessage('does not have a signature');

        $this->serializer->unserialize(base64_encode(serialize($this->env)));
    }

    public function testUnserializeErrorsWhenTheMessageDataHasBeenTamperedWith()
    {
        $this->expectException(SerializationError::class);
        $this->expectExceptionMessage('Invalid Message Signature');

        $s = explode('|', $this->serializer->serialize($this->env), 2);
        $s[1] = base64_encode(serialize(new \stdClass));
        $str = implode('|', $s);

        $this->serializer->unserialize($str);
    }

    protected function setUp() : void
    {
        $this->serializer = $this->createSerializer();
        $this->env = new DefaultEnvelope(new SimpleMessage('t'));
    }

    abstract protected function createSerializer() : Serializer;
}
