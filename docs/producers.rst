Producers
=========

Producers add messages to a driver backed for the :doc:`consumer <consumers>` to
pick up and handle.

.. php:interface:: Producer

    :namespace: PMG\\Queue

    .. php:method:: send(PMG\\Queue\\Message $message)

        Send a message to a driver backend.

        :param $message: The message to send into the queue
        :throws: PMG\\Queue\\Exception\\QueueNotFound if the message can't be routed to an appropriate queue.

The default producer implementation takes a driver and a router as its
constructor arguments and uses the router (explained below) to send its messages
into a drivers specific queue.

.. code-block:: php

    <?php

    use PMG\Queue\DefaultProducer;
    use PMG\Queue\Router\SimpleRouter;

    $router = new SimpleRouter('queueName');

    /** @var PMG\Queue\Driver $driver */
    $producer = new DefaultProducer($driver, $router);

.. _routers:

Routers
-------

``pmg/queue`` is built with multi-queue support in in mind. To accomplish that
on the producer side of things an implementation of ``PMG\Queue\Router`` is
used.

.. php:interface:: Router

    :namespace: PMG\\Queue

    .. php:method:: queueFor(PMG\\Queue\\Message $message)

        Looks a queue name for a given message.

        :param $message: the message to route
        :returns: A string queue name if found, ``null`` otherwise.
        :rtype: string or null


Routing all Message to a Single Queue
"""""""""""""""""""""""""""""""""""""

Use ``PMG\Queue\SimpleRouter``, which takes a queue name in the constructor
and always returns it.

.. code-block:: php

    <?php
    use PMG\Queue\Router\SimpleRouter;

    // all message will go in the "queueName" queue
    $router = new SimpleRouter('queueName');


Routing Messages Based on Their Name
""""""""""""""""""""""""""""""""""""

Use ``PMG\Queue\MappingRouter``, which takes a map of message name => queue name
pairs to its constructor.

.. code-block:: php

    <?php

    use PMG\Queue\Router\MappingRouter;

    $router = new MappingRouter([
        // the `SendAlert` message will go into the `Alerts` queue
        'SendAlert' => 'Alerts',
    ]);

Falling Back to a Default Queue
"""""""""""""""""""""""""""""""

To avoid ``QueueNotFound`` exceptions, it's often a good idea to use
``PMG\Queue\Router\FallbackRouter``.

.. code-block:: php

    <?php

    use PMG\Queue\DefaultProducer;
    use PMG\Queue\SimpleMessage;
    use PMG\Queue\Router\FallbackRouter;
    use PMG\Queue\Router\MappingRouter;

    $router = new FallbackRouter(new MappingRouter([
        'SendAlert' => 'Alerts',
    ]), 'defaultQueue');

    $producer = new DefaultProducer($driver, $router);

    // goes into the `Alerts` queue
    $producer->send(new SimpleMessage('SendAlert'));

    // goes into `defaultQueue`
    $producer->send(new SimpleMessage('OtherThing'));
