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

class SigningSerializerTest extends \PMG\Queue\UnitTestCase
{
    const ENC_MESSAGE   = 'czoxNDoidGhpcyBpcyBhIHRlc3QiOw==';
    const DEC_MESSAGE   = 'this is a test';
    const KEY           = 'test';

    private $wrapped, $serializer, $env;

    public function testSerializeGivesBackASignedString()
    {
        $this->wrapped->expects($this->once())
            ->method('serialize')
            ->with($this->identicalTo($this->env))
            ->willReturn(self::ENC_MESSAGE);
        $this->wrapped->expects($this->once())
            ->method('unserialize')
            ->with(self::ENC_MESSAGE)
            ->willReturn(self::DEC_MESSAGE);

        $s = $this->serializer->serialize($this->env);

        $this->assertEquals(self::DEC_MESSAGE, $this->serializer->unserialize($s));
    }

    /**
     * @expectedException PMG\Queue\Exception\SerializationError
     * @expectedExceptionMessage does not have a signature
     */
    public function testUnserializeWithUnsignedMessageCausesError()
    {
        $this->serializer->unserialize('asdf');
    }

    /**
     * @expectedException PMG\Queue\Exception\SerializationError
     * @expectedExceptionMessage signature does not match
     */
    public function testUnserializeWithInvalidSignatureCausesError()
    {
        $this->serializer->unserialize(sprintf(
            'd8551bf4b536cc7b53cd338b58416d0c4c755ed91e4e870fa6d6240dc4473836|%s',
            substr(self::ENC_MESSAGE, 0, 10)
        ));
    }

    protected function setUp()
    {
        $this->wrapped = $this->getMock(Serializer::class);
        $this->serializer = new SigningSerializer($this->wrapped, self::KEY);
        $this->env = new DefaultEnvelope(new SimpleMessage('t'));
    }
}
