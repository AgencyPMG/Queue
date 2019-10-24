Messages
========

Messages are plain PHP objects that may implement the ``PMG\Queue\Message``
interface. These objects are meant to be :ref:`serializable <serializers>` and
contain everything a :doc:`handler <handlers>` needs to do its job.

A message to send an alert to a user might look something like this:

.. _example-message:

Example Message
---------------

.. code-block:: php

    <?php
    final class SendAlert
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
contains a user's identifier rather than the full object. The :doc:`handler <handlers>`
would then look up the user.

See :doc:`consumers` and :doc:`producers` for more information about handlers
and messages fit into the system as a whole.

Message Names
-------------

To work with routing messages into certain queues in the :doc:`producers <producers>`
we rely on *message names*. By default a message name is an objects full
qualified class name (FQCN). Should a message need a different name, implement
the `PMG\Queue\Message` which has a single method: `getName`.

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

        public function getName() : string
        {
            return 'send_user_alert';
        }
    }
