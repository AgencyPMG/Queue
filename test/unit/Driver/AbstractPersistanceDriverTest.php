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

namespace PMG\Queue\Driver;

use PMG\Queue\Envelope;
use PMG\Queue\DefaultEnvelope;
use PMG\Queue\SimpleMessage;
use PMG\Queue\Serializer\Serializer;

// mostly a dirty hack to expose the `serialize` and `unserialize` methods
// as public. Also makes the serializer optional in the constructor so we can
// verify that the ABC errors when `parent::__construct($someSerializer)` is
// not called
abstract class _DriverAbc extends AbstractPersistanceDriver
{
    public function __construct(Serializer $serializer=null)
    {
        if ($serializer) {
            parent::__construct($serializer);
        }
    }

    public function serialize(Envelope $env)
    {
        return parent::serialize($env);
    }

    public function unserialize($str)
    {
        return parent::unserialize($str);
    }
}

class AbstractPersistanceDriverTest extends \PMG\Queue\UnitTestCase
{
    private $envelope;

    public function testDriversSerializeMethodsWorksWhenGivenASerializer()
    {
        list($serializer, $driver) = $this->createDriver();
        $serializer->expects($this->once())
            ->method('serialize')
            ->with($this->envelope)
            ->willReturn($envstr = serialize($this->envelope));

        $this->assertEquals($envstr, $driver->serialize($this->envelope));
    }

    public function testDriversUnserializeMethodsWorksWhenWhenASerializer()
    {
        list($serializer, $driver) = $this->createDriver();
        $serializer->expects($this->once())
            ->method('unserialize')
            ->with(serialize($this->envelope))
            ->willReturn($this->envelope);

        $this->assertEquals($this->envelope, $driver->unserialize(serialize($this->envelope)));
    }

    public function testSerializeErrorsWhenNoSerializerWasGivenToTheConstructor()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('does not have a serializer set');
        $this->createInvalidDriver()->serialize($this->envelope);
    }

    public function testUnserializeErrorsWhenNoSerializerWasGivenToTheConstructor()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('does not have a serializer set');
        $this->createInvalidDriver()->unserialize(serialize($this->envelope));
    }

    protected function setUp()
    {
        $this->envelope = new DefaultEnvelope(new SimpleMessage('Test'));
    }

    private function createDriver()
    {
        $serializer = $this->getMock(Serializer::class);
        $driver = $this->getMockForAbstractClass(_DriverAbc::class, [$serializer]);

        return [$serializer, $driver];
    }

    private function createInvalidDriver()
    {
        return $this->getMockForAbstractClass(_DriverAbc::class);
    }
}
