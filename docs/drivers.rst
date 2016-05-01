Drivers & Internals
===================

Behind the scenes :doc:`consumers <consumers>` and :doc:`producers <producers>`
use :ref:`driver <drivers>` and :ref:`envelopes <envelopes>` to do their work.

.. _drivers:

Drivers
-------

Drivers are the queue backend hidden behind the ``PMG\Queue\Driver`` interface.
``pmg/queue`` comes with two drivers built in: *memory* and *pheanstalk*
(beanstalkd).

Drivers have method for enqueuing and dequeueing messages as well as methods for
acknowledging a message is complete, retrying a message, or marking a message
as failed.

.. _envelopes:

Envelopes
---------

Envelopes wrap up :doc:`messages <messages>` to allow drivers to add additional
metadata. One example of such metadata is a :ref:`retry count <retrying>` that
the :doc:`consumers <consumers>` may use to determine if a message should be
retried. The :ref:`pheanstalk driver <pheanstalk-driver>` implements its own envelop
class so it can track the beanstalkd job identifier for the message.

Drivers are free to do whatever they need to do as long as their envelope
implements ``PMG\Queue\Envelope``.

Driver Implementations
----------------------

The core ``pmg/queue`` library provides a in memory driver and PMG maintains a
`driver for beanstalkd <https://github.com/AgencyPMG/queue-pheanstalk/tree/master/examples>`_
that uses the `pheanstalk <https://github.com/pda/pheanstalk>`_ library.

.. _testing:

The Memory Driver & Testing
^^^^^^^^^^^^^^^^^^^^^^^^^^^

The memory driver is provided to make prototyping and testing easy. It uses
`SplQueue <http://php.net/manual/en/class.splqueue.php>`_ instances and only
keeps messages in memory.


.. code-block:: php

    <?php
    use PMG\Queue\DefaultConsumer;
    use PMG\Queue\Driver\MemoryDriver;

    // ...

    $driver = new MemoryDriver();

    // $executor instanceof PMG\Queue\MessageExecutor
    $consumer = new DefaultConsumer($driver, $executor);

The memory driver isn't extrodinary useful outside of testing. For instance,
while doing end to end tests, you may want to switch out your producers library
to use the memory driver then verify the expected messages when into it.

.. code-block:: php

    <?php
    use PMG\Queue\Driver\MemoryDriver;

    class SomeTest extends \PHPUnit_Framework_TestCase
    {
        const TESTQ = 'TestQueue';

        /** @var MemoryDriver $driver */
        private $driver;

        public function testSomething()
        {
            // imagine some stuff happened before this, now we need to verify that

            $envelope = $this->driver->dequeue(self::TESTQ);
            
            $this->assertNotNull($envelope);
            $msg = $envelope->unwrap();
            $this->assertInstanceOf(SendAlert::class, $msg);
            $this->assertEquals(123, $msg->getUserId());
        }

    }


.. _pheanstalk-driver:

Pheanstalk Driver
^^^^^^^^^^^^^^^^^

The pheanstalk driver is backed by `beanstalkd <http://kr.github.io/beanstalkd/>`_
and is a *persistent* driver: messages persist across multiple requests or queue
runs.

To use it, use composer to install ``pmg/queue-pheanstalk`` and pass an instance
of ``Pheanstalk\Pheanstalk`` and a :ref:`serializer <serializers>` to its constructor.

.. code-block:: php

    <?php
    use Pheanstalk\Pheanstalk;
    use PMG\Queue\Driver\PheanstalkDriver;
    use PMG\Queue\Driver\Serializer\NativeSerializer;

    $driver = new PheanstalkDriver(
        new Pheanstalk('localhost', 11300),
        new NativeSerializer('this is a key used to sign messages')
    );


See the `pheanstalk driver repository <https://github.com/AgencyPMG/queue-pheanstalk#quick-example>`_
for more information and examples.


.. _serializers:

Serializers
-----------

Persistent drivers require some translation from :ref:`envelopes <envelopes>`
and :doc:`messages <messages>` to something the persistent backend can store.
Similarly, whatever is stored in the queue backend needs to be turned back into
a message. **Serializers** make that happen.

All serializers implements ``PMG\Queue\Serializer\Serializer`` and one
implementation is provied by default: ``NativeSerializer``.

``NativeSerializer`` uses PHP's build in ``serialize`` and ``unserialize``
functions. Serialized envelopes are base64 encoded and signed (via an HMAC) with
a key given to ``NativeSerializer`` in its constructor. The signature is a way
to authenticate the message (make sure it came from a source known to use).

.. code-block:: php

    <?php
    use PMG\Queue\Serializer\NativeSerializer;

    $serializer = new NativeSerializer('this is the key');

    // ...


Allowed Classes in PHP 7
^^^^^^^^^^^^^^^^^^^^^^^^

``NativeSerializer`` supports PHP 7's ``allowed_classes`` option in
``unserialize`` to whitelist classes. Just pass an array of message class names
as the second argument to ``NativeSerializer``'s constructor.

Because drivers have their own envelope classes, the :ref:`pheanstalk driver <pheanstalk-driver>`
(or any other drivers that extend ``PMG\Queue\Driver\AbstractPersistanceDriver``)
provides a static ``allowedClasses`` method that returns an array of envelope
classes to whitelist.

.. code-block:: php

    <?php
    use PMG\Queue\Serializer\NativeSerializer;
    use PMG\Queue\Driver\PheanstalkDriver;

    $serializer = new NativeSerializer('YourSecretKeyHere', array_merge([
        // your message classes
        SendAlert::class,
        // ...
    ], PheanstalkDriver::allowedClasses()));


Implementing Your Own Drivers
-----------------------------

Persistent drivers are not required to use serializers (or anything else), but
if they do ``PMG\Queue\Driver\AbstractPersistanceDriver`` provides helpers for
the usage of serializers.
