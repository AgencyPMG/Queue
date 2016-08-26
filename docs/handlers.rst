Message Handlers
================

A message handler is used by ``DefaultConsumer`` to do the actual work of
processing a message. Handlers implement ``PMG\Queue\MessageHandler`` which
accepts a message and a set of options from the the consumer as its arguments.

Every single message goes through a single handler. It's up to that handler to
figure out how to deal with each message appropriately.

Callable Handler
----------------

The simplest handler could just be a callable that invokes the provided callback
with the message.

.. code-block:: php

    <?php

    use PMG\Queue\DefaultConsumer;
    use PMG\Queue\Message;
    use PMG\Queue\Driver\MemoryDriver;
    use PMG\Queue\Handler\CallableHandler;

    $handler = new CallableHandler(function (Message $msg) {
        switch ($msg->getName()) {
            case 'SendAlert':
                sendAnAlertSomehow($msg);
                break;
            case 'OtherMessage':
                handleOtherMessageSomehow($msg);
                break;
        }
    });

    $consumer = new DefaultConsumer(new MemoryDriver(), $handler);

Multiple Handlers with Mapping Handler
--------------------------------------

The above `switch` statement is a lot of boilerplaint, so PMG provies a
`mapping handler <https://github.com/AgencyPMG/queue-mapping-handler>`_
that looks up callables for a message based on its name. For example,
here's a callable for the :ref:`send alert message <send-alert-message>`.

.. code-block:: php

    <?php

    final class SendAlertHandler
    {
        private $users;
        private $mailer;

        public function __construct(UserRepository $users, \Swift_Mailer $mailer)
        {
            $this->users = $users;
            $this->mailer = $mailer;
        }

        public function __invoke(SendAlert $message)
        {
            $user = $this->users->getByIdentifierOrError($message->getUserId());

            $this->mailer->send(
                \Swift_Message::newInstance()
                    ->setTo([$user->getEmail()])
                    ->setFrom(['help@example.com'])
                    ->setSubject('Hello')
                    ->setBody('World')
            );
        }
    }

Now pull in the mapping handler with ``composer require pmg/queue-mapping-handler`` 
and we can integrate the callable above with it.

.. code-block:: php

    <?php

    use PMG\Queue\DefaultConsumer;
    use PMG\Queue\Handler\MappingHandler;

    $handler = MappingHandler::fromArray([
        'SendAlert' => new SendAlertHandler(/*...*/),
        //'OtherMessage' => new OtherMessageHandler()
        // etc
    ]);

    /** @var PMG\Queue\Driver $driver */
    $consumer = new DefaultConsumer($driver, $handler);

Using Tactician to Handle Messages
----------------------------------

`Tactician <https://tactician.thephpleague.com/>`_ is a command bus from The PHP
League. You can use it to do message handling with the queue.

.. code-block:: php

    <?php

    use League\Tactician\CommandBus;
    use PMG\Queue\DefaultConsumer;
    use PMG\Queue\Handler\TaticianHandler;

    // use the same commang bus instance each time
    $handler = TaticianHandler::fromCommandBus(new CommandBus(/*...*/));

    // or you can provide a factory callback to create the command bus
    // on demand for each handle This is useful if you're using the
    // `PcntlForkingHandler` to handle messages in separate processes
    $handler = new TacticianHandler(function (array $optionsPassedToHandle) {
        return new CommandBus(/*...*/);
    });

    /** @var PMG\Queue\Driver $driver */
    $consumer = new DefaultConsumer($driver, $handler);

Handling Messages in Separate Processes
---------------------------------------

To handle messages in a forked process use the ``PcntlForkingHandler``
decorator.

.. code-block:: php

    <?php

    use PMG\Queue\Handler\MappingHandler;
    use PMG\Queue\Handler\PcntlForkingHandler;

    // create an actual handler
    $realHandler = MappingHandler::fromArray([
        // ...
    ]);

    // decorate it with the forking handler
    $handler = new PcntlForkingHandler($realHandler);

Forking is useful for memory management, but requires some consideration. For
instance, database connections might need to be re-opened in the forked process.
In such cases, the best bet is to simply create the resources on demand. that's
why the ``TaticianHandler`` above takes a factory callable by default.
