Messages & Handlers
===================

Messages are objects that implement the ``PMG\Queue\Message`` interface. These
objects are meant to be :doc:`serializable <serializers>` and contain everything
you need for a :ref:`handler <handlers>` to do its job.

A message to send an alert to a user might look something like this:

.. code-block:: php

    <?php
    use PMG\Queue\Message;

    final class SendAlert implements Message
    {
        private $userId;

        public function __construct($userId)
        {
            $this->userId = $userId;
        }

        public function getUserId()
        {
            return $this->userId;
        }
    }

Because messages are serialized to be put in a persistent backend they shouldn't
include objects that require state. In the example above the message just
contains a user's identifier rather than the full object. The handler would
then look up the user.

.. _handlers:

Handlers
--------

A message handler is just a callable that accepts a message as its only
argument. The queue backend doesn't care if it's an object, function, closure,
or method: just that it's callable. Handlers are found via a
:ref:`handler resolver <resolvers>` and invoked with an :ref:`executor <executors>`.

An example handler for our message above might look like this:

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

See :doc:`consumers` and :doc:`producers` for more information about handlers
and messages fit into the system as a whole.
