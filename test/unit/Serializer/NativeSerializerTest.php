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

use PHPUnit\Framework\MockObject\MockObject;
use PMG\Queue\Envelope;
use PMG\Queue\DefaultEnvelope;
use PMG\Queue\SimpleMessage;
use PMG\Queue\Exception\MissingSignature;
use PMG\Queue\Exception\NotAnEnvelope;
use PMG\Queue\Exception\SerializationError;
use PMG\Queue\Exception\InvalidSignature;
use PMG\Queue\Signer\Signer;

class NativeSerializerTest extends \PMG\Queue\UnitTestCase
{
    const SIG = 'someHmac';

    /**
     * @var Signer&MockObject
     */
    private Signer $signer;

    private NativeSerializer $serializer;

    private DefaultEnvelope $env;

    private string $envMessage;

    private string $sigMessage;

    public function testSerializeNativeSerializersAndSignsTheMessageBeforeReturningIt()
    {
        $this->willSignMessage($this->envMessage);

        $result = $this->serializer->serialize($this->env);

        $this->assertStringContainsString(self::SIG, $result);
        $this->assertStringContainsString($this->envMessage, $result);
    }

    public function testUnserializeErrorsWhenTheMessageSignatureIsNotPresent()
    {
        $this->expectException(MissingSignature::class);
        $this->expectExceptionMessage('does not have a signature');

        $this->serializer->unserialize($this->envMessage);
    }

    public function testUnserializeErrorsWhenTheSignatureIsInvalid()
    {
        $this->expectException(InvalidSignature::class);
        $this->expectExceptionMessage('Invalid Message Signature');
        $this->signer->expects($this->once())
            ->method('verify')
            ->with('invalid', $this->envMessage)
            ->willReturn(false);

        $this->serializer->unserialize('invalid|'.$this->envMessage);
    }

    public function testUnserializeErrorsWhenTheSerializedStringIsInvalid()
    {
        $this->expectException(SerializationError::class);
        $this->expectExceptionMessage('Error unserializing message:');
        $env = base64_encode('a:');
        $this->willVerifyMessage($env);

        $this->serializer->unserialize(self::SIG.'|'.$env);
    }

    public function testUnserializeErrorsWhenTheClassUnserializeIsNotAnEnvelope()
    {
        $this->expectException(NotAnEnvelope::class);
        $this->expectExceptionMessage('an instance of');
        $env = base64_encode(serialize(new \stdClass()));
        $this->willVerifyMessage($env);

        $this->serializer->unserialize(self::SIG.'|'.$env);
    }

    public function testUnserializeReturnsTheUnserializeEnvelopeWhenSuccessful()
    {
        $this->willVerifyMessage($this->envMessage);

        $result = $this->serializer->unserialize($this->sigMessage);

        $this->assertEquals($this->env, $result);
    }

    protected function setUp() : void
    {
        $this->signer = $this->createMock(Signer::class);
        $this->serializer = new NativeSerializer($this->signer);
        $this->env = new DefaultEnvelope(new SimpleMessage('t'));
        $this->envMessage = base64_encode(serialize($this->env));
        $this->sigMessage = self::SIG.'|'.$this->envMessage;
    }

    private function willSignMessage(string $message)
    {
        $this->signer->expects($this->once())
            ->method('sign')
            ->with($message)
            ->willReturn(self::SIG);
    }

    private function willVerifyMessage(string $message)
    {
        $this->signer->expects($this->once())
            ->method('verify')
            ->with(self::SIG, $message)
            ->willReturn(true);
    }
}
