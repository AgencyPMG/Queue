Consumers
=========

Implementations of ``PMG\Queue\Consumer`` pull message out of a driver backend
and handle (process) them in some way. The default consumer accomplishes this a
:doc:`message handler <handlers>`.

In all cases ``$queueName`` in the consume should correspond to queues into
which your :doc:`producer <producers>` put messages.

.. php:interface:: Consumer

    :namespace: PMG\\Queue

    .. php:method:: run($queueName, MessageLifecycle $lifecycle=null)

        Consume and handle messages from $queueName indefinitely.

        :param string $queueName: The queue from which the messages will be processed.
        :param MessageLifecycle|null $lifecycle: An optional message lifecycle.
        :throws: ``PMG\Queue\Exception\DriverError`` If some things goes wrong
            with the underlying driver. Generally this happens if the persistent
            backend goes down or is unreachable. Without the driver the consumer
            can't do its work.
        :returns: An exit code
        :rtype: int

    .. php:method:: once($queueName, MessageLifecycle $lifecycle=null)

        Consume and handle a single message from $queueName

        :param string $queueName: The queue from which the messages will be processed.
        :param MessageLifecycle|null $lifecycle: An optional message lifecycle.
        :throws: PMG\\Queue\\Exception\\DriverError If some things goes wrong
            with the underlying driver. Generally this happens if the persistent
            backend goes down or is unreachable. Without the driver the consumer
            can't do its work.
        :returns: True or false to indicate if the message was handled successfully.
            null if no message was handled.
        :rtype: boolean or null

    .. php:method:: stop(int $code)

        Used on a running consumer this will tell it to gracefully stop on its
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

When a message fails -- by throwing an exception or returns false from a
``MessageHandler`` -- the consumer puts it back in the queue to retry up to 5
times by default. This behavior can be adjusted by providing a ``RetrySpec`` as
the third argument to ``DefaultConsumers`` constructor. `pmg/queue` provides a
few by default.

Retry specs look at ``PMG\Queue\Envelope`` instances, not raw messages. See the
:ref:`internals documentation <envelopes>` for more info about them.

.. php:interface:: RetrySpec

    :namespace: PMG\\Queue


    .. php:method:: canRetry(PMG\\Queue\\Envelope $env)

        Inspects an envelop to see if it can retry again.

        :param $env: The message envelope to check
        :returns: true if the message can be retried, false otherwise.
        :rtype: boolean

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

A ``MessageLifecycle`` implementation provides a look into a message as it
moves through the consumer. The goal is to allow an application to hook into a
consumer processing to take actions they want. Say an application requires
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

        public function failed(Message $message, Consumer $consumer, bool $isRetrying)
        {
            if (!$isRetrying) {
                $this->notifier->send(new Notification(sprintf(
                    '%s message failed',
                    $message->getName()
                )));
            }
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

We've found at PMG that most times queue name is a detail that simply does not
matter to the application itself. It's just a way to distribute work.

Provided Message Lifecycles
"""""""""""""""""""""""""""

A ``NullLifecycle``, mentioned above, that does nothing. This makes a convenient
base class to extend and implement what methods your application requires.

Additionally there are a few other provided ``MessageLifecycle`` implementations.

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
    $lifecycle = DelegatingLifecycle::fromArray([
        new NotifyingLifecycle(/* ... */),
        new SomeOtherLifecycle(),
    ]);

``MappingLifecycle`` proxies to other lifecycles based on the incoming message
name. Use this if specific ``MessageLifecycle`` implementations need to fire
for specific messages. In the example below ``NotifyingLifecycle`` would track
``messageA`` through its lifecycle and ``SomeOtherLifecycle`` would track
``messageB``. Any other message would fallback to ``FallbackLifecycle``.

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

These two implementations could be combined as well.

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

Extend ``PMG\\Queue\\AbstractConsumer`` to make things easy and
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

        // constructor that takes a consumer and dispatcher to set the props ^

        public function once($queueName)
        {
            $this->events->dispatch('queue:before_once', new Event());
            $this->wrapped->once($queueName);
            $this->events->disaptch('queue:after_once', new Event());
        }
    }
