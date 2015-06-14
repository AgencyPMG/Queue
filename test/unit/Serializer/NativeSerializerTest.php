<?php
/**
 * This file is part of PMG\Queue
 *
 * Copyright (c) PMG <https://www.pmg.com>
 *
 * For full copyright information see the LICENSE file distributed
 * with this source code.
 *
 * @license     http://opensource.org/licenses/MIT MIT
 */

namespace PMG\Queue\Serializer;

use PMG\Queue\DefaultEnvelope;
use PMG\Queue\SimpleMessage;

class NativeSerializerTest extends \PMG\Queue\UnitTestCase
{
    private $serializer;

    public function testSerialzeReturnsASerializedMessageObject()
    {
        $s = $this->serializer->serialize(new DefaultEnvelope(new SimpleMessage('t')));
        $this->assertInternalType('string', $s);
        $this->assertInstanceOf(DefaultEnvelope::class, unserialize(base64_decode($s)));
    }

    /**
     * @expectedException PMG\Queue\Exception\SerializationError
     */
    public function testUnserializeErrorsWhenABadSerializeStringIsGiven()
    {
        $this->serializer->unserialize(base64_encode('a:'));
    }

    public static function notEnvelopes()
    {
        return [
            [1],
            [1.0],
            [true],
            [null],
            [new \stdClass],
        ];
    }

    /**
     * @dataProvider notEnvelopes
     * @expectedException PMG\Queue\Exception\SerializationError
     */
    public function testUnserializeErrorsWhenANonMessageIsTheResult($env)
    {
        $this->serializer->unserialize(base64_encode(serialize($env)));
    }

    public function testUnserializeReturnsTheMessageWhenDeserializationIsSuccessful()
    {
        $res = $this->serializer->unserialize(base64_encode(serialize($this->env)));

        $this->assertInstanceOf(DefaultEnvelope::class, $res);
    }

    protected function setUp()
    {
        $this->serializer = new NativeSerializer();
        $this->env = new DefaultEnvelope(new SimpleMessage('t'));
    }
}
