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

use PMG\Queue\Envelope;
use PMG\Queue\DefaultEnvelope;
use PMG\Queue\SimpleMessage;
use PMG\Queue\Exception\SerializationError;

/**
 * Closer to an integration test than a unit test.
 */
class NativeSerializerTest extends \PMG\Queue\UnitTestCase
{
    const KEY = 'SuperSecretKey';

    private $serializer;

    public function testSerializeReturnsAStringThatCanBeUnserialized()
    {
        $s = $this->serializer->serialize($this->env);
        $this->assertInternalType('string', $s);

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
        $this->expectExceptionMessage('Invalid HMAC Signature');

        $s = explode('|', $this->serializer->serialize($this->env), 2);
        $s[1] = base64_encode(serialize(new \stdClass));
        $str = implode('|', $s);

        $this->serializer->unserialize($str);
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
     */
    public function testUnserializeErrorsWhenANonMessageIsTheResult($env)
    {
        $this->expectException(SerializationError::class);
        $this->expectExceptionMessage(sprintf('an instance of "%s"', Envelope::class));
        $env = base64_encode(serialize($env));
        $this->serializer->unserialize(sprintf(
            '%s|%s',
            hash_hmac(NativeSerializer::HMAC_ALGO, $env, self::KEY, false),
            $env
        ));
    }

    public function testUnserializeErrorsWhenTheSerializedStringIsInvalid()
    {
        $this->expectException(SerializationError::class);
        $this->expectExceptionMessage('Error unserializing message:');
        $env = base64_encode('a:');
        $this->serializer->unserialize(sprintf(
            '%s|%s',
            hash_hmac(NativeSerializer::HMAC_ALGO, $env, self::KEY, false),
            $env
        ));
    }

    public function testPhpLessThanSevenErrorsIfAllowedClassesAreGivenToTheSerializer()
    {
        $this->skipIfPhp7();
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('$allowedClasses');

        new NativeSerializer(self::KEY, ['stdClass']);
    }

    public function testAllowedClassesUnserializesClassesNotInWhitelistToIncompleteClass()
    {
        $this->skipIfPhp5();
        $this->expectException(SerializationError::class);
        $this->expectExceptionMessage('expected its message property to be unserialized with an instance of PMG\Queue\Message');

        $s = new NativeSerializer(self::KEY,  [DefaultEnvelope::class]);
        $env = $s->unserialize(base64_encode(serialize($this->env)));

        $this->assertInstanceOf(DefaultEnvelope::class, $env);
    }

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
     * @dataProvider notStrings
     */
    public function testSerializersCannotBeCreatedWithANonStringKey($key)
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('$key must be a string');

        new NativeSerializer($key);
    }

    public function testSerializersCannotBeCreatedWithEmptyKeys()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('$key cannot be empty');

        new NativeSerializer('');
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

    protected function setUp()
    {
        $this->serializer = new NativeSerializer(self::KEY);
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
