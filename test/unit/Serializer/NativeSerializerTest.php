<?php
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

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage $allowedClasses
     */
    public function testPhpLessThanSevenErrorsIfAllowedClassesAreGivenToTheSerializer()
    {
        $this->skipIfPhp7();

        new NativeSerializer(['stdClass']);
    }

    /**
     * @expectedException PMG\Queue\Exception\SerializationError
     * @expectedExceptionMessage unserialized with an instance of PMG\Queue\Message
     */
    public function testAllowedClassesUnserializesClassesNotInWhitelistToIncompleteClass()
    {
        $this->skipIfPhp5();

        $s = new NativeSerializer([DefaultEnvelope::class]);
        $env = $s->unserialize(base64_encode(serialize($this->env)));

        $this->assertInstanceOf(DefaultEnvelope::class, $env);
    }

    protected function setUp()
    {
        $this->serializer = new NativeSerializer();
        $this->env = new DefaultEnvelope(new SimpleMessage('t'));
    }

    private function skipIfPhp7()
    {
        if (self::isPhp7()) {
            $this->markTestSkipped(sprintf('PHP < 7.X is required, have %s', PHP_VERSION));
        }
    }

    private function skipIfPhp5()
    {
        if (!self::isPhp7()) {
            $this->markTestSkipped(sprintf('PHP 5.X is required, have %s', PHP_VERSION));
        }
    }

    private static function isPhp7()
    {
        return PHP_VERSION_ID >= 70000;
    }
}
