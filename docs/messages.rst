Messages
========

Messages are objects that implement the ``PMG\Queue\Message`` interface. These
objects are meant to be :ref:`serializable <serializers>` and contain everything
you need for a :doc:`handler <handlers>` to do its job.

A message to send an alert to a user might look something like this:

.. _example-message:

Example Message
---------------

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
contains a user's identifier rather than the full object. The :doc:`handler <handlers>`
would then look up the user.

See :doc:`consumers` and :doc:`producers` for more information about handlers
and messages fit into the system as a whole.
