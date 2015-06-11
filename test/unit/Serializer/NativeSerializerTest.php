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

class _NativeSerializerMessage implements \PMG\Queue\Message
{
    use \PMG\Queue\MessageTrait;
}

class NativeSerializerTest extends \PMG\Queue\UnitTestCase
{
    private $serializer;

    public function testSerialzeReturnsASerializedMessageObject()
    {
        $s = $this->serializer->serialize(new _NativeSerializerMessage());
        $this->assertInternalType('string', $s);
        $this->assertInstanceOf(_NativeSerializerMessage::class, unserialize($s));
    }

    /**
     * @expectedException PMG\Queue\Exception\SerializationError
     */
    public function testUnserializeErrorsWhenABadSerializeStringIsGiven()
    {
        $this->serializer->unserialize('a:');
    }

    public static function notMessages()
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
     * @dataProvider notMessages
     * @expectedException PMG\Queue\Exception\SerializationError
     */
    public function testUnserializeErrorsWhenANonMessageIsTheResult($msg)
    {
        $this->serializer->unserialize(serialize($msg));
    }

    public function testUnserializeReturnsTheMessageWhenDeserializationIsSuccessful()
    {
        $res = $this->serializer->unserialize(serialize(new _NativeSerializerMessage()));

        $this->assertInstanceOf(_NativeSerializerMessage::class, $res);
    }

    protected function setUp()
    {
        $this->serializer = new NativeSerializer();
    }
}
