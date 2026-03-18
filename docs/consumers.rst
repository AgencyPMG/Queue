Consumers
=========

Implementations of ``PMG\Queue\Consumer`` pull messages out of a driver backend
and handle (process) them in some way. The default consumer accomplishes this
through a :doc:`message handler <handlers>`.

In all cases, ``$queueName`` in the consumer should correspond to queues into
which your :doc:`producer <producers>` puts messages.

.. php:interface:: Consumer

    :namespace: PMG\\Queue

    .. php:method:: run($queueName, MessageLifecycle $lifecycle=null)

        Consume and handle messages from $queueName indefinitely.

        :param string $queueName: The queue from which the messages will be processed.
        :param MessageLifecycle|null $lifecycle: An optional message lifecycle.
        :throws: ``PMG\Queue\Exception\DriverError`` If something goes wrong
            with the underlying driver. Generally this happens if the persistent
            backend goes down or is unreachable. Without the driver, the consumer
            can't do its work.
        :returns: An exit code
        :rtype: int

    .. php:method:: once($queueName, MessageLifecycle $lifecycle=null)

        Consume and handle a single message from $queueName.

        :param string $queueName: The queue from which the messages will be processed.
        :param MessageLifecycle|null $lifecycle: An optional message lifecycle.
        :throws: PMG\\Queue\\Exception\\DriverError If something goes wrong
            with the underlying driver. Generally this happens if the persistent
            backend goes down or is unreachable. Without the driver, the consumer
            can't do its work.
        :returns: True or false to indicate whether the message was handled successfully.
            Null if no message was handled.
        :rtype: boolean or null

    .. php:method:: stop(int $code)

        Calling this on a running consumer tells it to stop gracefully on its
        next iteration.

        :param int $code: The exit code to return from `run`


The script to run your consumer might look something like this. Check out the
:doc:`handlers <handlers>` documentation for more information about what
``$handler`` is below.

.. code-block:: php

    <?php

    use PMG\Queue\DefaultConsumer;
    use PMG\Queue\Driver\MemoryDriver;

    $driver = new MemoryDriver();

    /** @var PMG\Queue\MessageHandler $handler */
    $consumer = new DefaultConsumer($driver, $handler);

    exit($consumer->run(isset($argv[1]) ? $argv[1] : 'defaultQueue'));

.. _retrying:

Retrying Messages
-----------------

When a message fails, either by throwing an exception or by returning ``false``
from a ``MessageHandler``, the consumer puts it back in the queue and retries
it up to five times by default. This behavior can be adjusted by providing a
``RetrySpec`` as the third argument to the ``DefaultConsumer`` constructor.
``pmg/queue`` provides a few retry specs by default. Additionally,
``RetrySpec`` provides a method for calculating how long a message should be
delayed before it can be retried.

    Not all :ref:`drivers <drivers>` support retry delays. Check the driver's
    documentation for more details.

Retry specs look at ``PMG\Queue\Envelope`` instances, not raw messages. See the
:ref:`internals documentation <envelopes>` for more info about them.

.. php:interface:: RetrySpec

    :namespace: PMG\\Queue


    .. php:method:: canRetry(PMG\\Queue\\Envelope $env)

        Inspects an envelope to see whether it can be retried again.

        :param $env: The message envelope to check
        :returns: true if the message can be retried, false otherwise.
        :rtype: boolean

    .. php:method:: retryDelay(PMG\\Queue\\Envelope $env)

        Determines how long the message should be delayed before retrying again.
        Not all queue drivers will support retry delays.

        :param $env: The message envelope for which the delay should be calculated
        :returns: The delay in seconds
        :rtype: int


Limited Retries
"""""""""""""""

Use ``PMG\\Queue\\Retry\\LimitedSpec``.

.. code-block:: php

    <?php

    use PMG\Queue\DefaultConsumer;
    use PMG\Queue\Retry\LimitedSpec;

    // five retries by default. This is what the consumer does automatically
    $retry = new LimitedSpec();

    // Or limit to a specific number of retries
    $retry = new LimitedSpec(2);

    // May specify a delay as well, the default is no delay
    // here the delay is 20 seconds and limited to five retries
    $retry = new LimitedSpec(5, 20);

    // $driver and $handler as above
    $consumer = new DefaultConsumer($driver, $handler, $retry);


Never Retry a Message
"""""""""""""""""""""

Sometimes you don't want to retry a message, for those cases use
``PMG\\Queue\\Retry\\NeverSpec``.

.. code-block:: php

    <?php

    use PMG\Queue\DefaultConsumer;
    use PMG\Queue\Retry\NeverSpec;

    $retry = new NeverSpec();

    // $driver and $handler as above
    $consumer = new DefaultConsumer($driver, $handler, $retry);

Logging
-------

When something goes wrong ``DefaultConsumer`` logs it with a
`PSR-3 Logger <http://www.php-fig.org/psr/psr-3/>`_ implementation. The default
is to use a `NullLogger`, but you can provide your own logger as the fourth
argument to ``DefaultConsumer``'s constructor.

.. code-block:: php

    <?php

    use PMG\Queue\DefaultConsumer;

    $monolog = new Monolog\Logger('yourApp');

    // $driver, $handler, $retry as above
    $consumer = new DefaultConsumer($driver, $handler, $retry, $monolog);


Using Message Lifecycles
------------------------

A ``MessageLifecycle`` implementation provides visibility into a message as it
moves through the consumer. The goal is to allow an application to hook into
consumer processing and take the actions it needs. Say an application requires
sending a notification when a message fails and will not be retried.

.. code-block:: php

    <?php

    use PMG\Queue\Lifecycle\NullLifecycle;
    use App\Notifications\Notifier;
    use App\Notifications\Notification;

    // NullLifecycle provides all the lifecycle methods, so only what's
    // required can be implemented here.
    class NotifyingLifecycle extends NullLifecycle
    {
        /** @var Notifier */
        private $notifier;

        // constructor, etc

        public function failed(Message $message, Consumer $consumer)
        {
            $this->notifier->send(new Notification(sprintf(
                '%s message failed',
                $message->getName()
            )));
        }
    }

This custom lifecycle can be passed into ``Consumer::run`` or ``Consumer::once``.

.. code-block:: php

    <?php

    /** @var PMG\Queue\Consumer $consumer */
    $consumer->run('someQueue', new NotifyingLifecycle(/* ... */));

Lifecycles Don't Know About Queue Names
"""""""""""""""""""""""""""""""""""""""

This is on purpose. Because lifecycle objects are passed into consumers at the
same time as the queue name, it's up to the implementation to decide if they
care about that detail. If the implementation does care, it can take the queue
name as a constructor argument.

We've found at PMG that, most of the time, the queue name is a detail that does
not matter to the application itself. It's just a way to distribute work.

Provided Message Lifecycles
"""""""""""""""""""""""""""

A ``NullLifecycle``, mentioned above, does nothing. This makes a convenient
base class to extend and implement what methods your application requires.

There are a few other provided ``MessageLifecycle`` implementations.

``DelegatingLifecycle`` proxies to multiple child lifecycles. Use this to compose
other lifecycles together. In the example below, both ``NotifyingLifecycle`` and
``SomeOtherLifecycle`` would be called for each stage through which the message
moves.

.. code-block:: php

    <?php

    use PMG\Queue\Lifecycle\DelegatingLifecycle;

    $lifecycle = new DelegatingLifecycle(
        new NotifyingLifecycle(/* ... */), // see above
        new SomeOtherLifecycle()
    );

    // Or create from an array
    $lifecycle = DelegatingLifecycle::fromIterable([
        new NotifyingLifecycle(/* ... */),
        new SomeOtherLifecycle(),
    ]);

``MappingLifecycle`` proxies to other lifecycles based on the incoming message
name. Use this if specific ``MessageLifecycle`` implementations need to fire
for specific messages. In the example below ``NotifyingLifecycle`` would track
``messageA`` through its lifecycle and ``SomeOtherLifecycle`` would track
``messageB``. Any other message would fall back to ``FallbackLifecycle``.

.. code-block:: php

    <?php

    use PMG\Queue\Lifecycle\MappingLifecycle;

    // can use an array or `ArrayAccess` implementation here
    $lifecycle = new MappingLifecycle([
        'messageA' => new NotifyingLifecycle(/* ... */), 
        'messageB' => new SomeOtherLifecycle(),
    ], new FallbackLifecycle());

    // or omit the fallback and it will default to `NullLifecycle`
    // and do nothing.
    $lifecycle = new MappingLifecycle([
        'messageA' => new NotifyingLifecycle(/* ... */), 
        'messageB' => new SomeOtherLifecycle(),
    ]);

You can combine these two implementations as well.

.. code-block:: php

    <?php

    use PMG\Queue\Lifecycle\DelegatingLifecycle;
    use PMG\Queue\Lifecycle\MappingLifecycle;

    $lifecycle = new DelegatingLifecycle(
        new FooLifecycle(),
        new MappingLifecycle([
            'messageA' => new DelegatingLifecycle(
                new BarLifecycle(),
                new BazLifecycle()
            ),
        ])
    );

Build Custom Consumers
----------------------

Extend ``PMG\\Queue\\AbstractConsumer`` to make implementation easier and
implement the ``once`` method. Here's an example that decorates another
``Consumer`` with events.

.. code-block:: php

    <?php

    use PMG\Queue\AbstractConsumer;
    use PMG\Queue\Consumer;
    use PMG\Queue\Message;
    use Symfony\Component\EventDispatcher\Event;
    use Symfony\Component\EventDispatcher\EventDispatcherInterface;

    final class EventingConsumer extends AbstractConsumer
    {
        /** @var Consumer */
        private $wrapped;

        /** @var EventDispatcherInterface $events */

        // constructor omitted; it should accept a consumer and dispatcher and
        // assign the properties above.

        public function once($queueName)
        {
            $this->events->dispatch('queue:before_once', new Event());
            $this->wrapped->once($queueName);
            $this->events->dispatch('queue:after_once', new Event());
        }
    }
