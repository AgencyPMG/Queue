Message Handlers
================

A message handler is used by ``DefaultConsumer`` to do the actual work of
processing a message. Handlers implement ``PMG\Queue\MessageHandler`` which
accepts a message and a set of options from the the consumer as its arguments.

Every single message goes through a single handler. It's up to that handler to
figure out how to deal with each message appropriately.

.. php:interface:: MessageHandler

    :namespace: PMG\\Queue

    An object that can handle (process or act upon) a single message.

    .. php:method:: handle(PMG\\Queue\\Message $handle, array $options=[])

        :param $handle: The message to handle.
        :param $options: A set of options from the consumer.
        :return: A boolean indicated whether the message was handled successfully.
        :rtype: boolean


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
here's a callable for the :ref:`send alert message <example-message>`.

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

.. code-block:: bash

    composer install pmg/queue-tactician

Use the same command bus with each message.

.. code-block:: php

    <?php

    use League\Tactician\CommandBus;
    use PMG\Queue\DefaultConsumer;
    use PMG\Queue\Handler\TaticianHandler;

    $handler = new TacticianHandler(new CommandBus(/* ... */));

    /** @var PMG\Queue\Driver $driver */
    $consumer = new DefaultConsumer($driver, $handler);

Alternative, you can create a new command bus to handle each message with
`CreatingTacticianHandler`. This is useful if you're using
:ref:`forking child processes <forking_handler>` to handle messages.

.. code-block:: php

    <?php

    use League\Tactician\CommandBus;
    use PMG\Queue\DefaultConsumer;
    use PMG\Queue\Handler\CreatingTaticianHandler;

    $handler = new TacticianHandler(function () {
        return new CommandBus(/* ... */);
    });

    /** @var PMG\Queue\Driver $driver */
    $consumer = new DefaultConsumer($driver, $handler);

.. _forking_handler:

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

In cases where a process fails to fork, a ``PMG\Queue\Exception\CouldNotFork``
exception will be thrown and the consumer will exit with an unsuccessful status
code. Your process manager (supervisord, upstart, systemd, etc) should be
configured to restart the consumer when that happens.
