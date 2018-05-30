.. PMG Queue documentation master file, created by
   sphinx-quickstart on Sat Apr 30 11:16:45 2016.
   You can adapt this file completely to your liking, but it should at least
   contain the root `toctree` directive.

PMG Queue
=========

``pmg/queue`` is a production ready queue framework that powers many internal
projects at `PMG <https://www.pmg.com/>`_.

It's simple and extensible a number of features we've found to be the most
useful including automatic retries and multi-queue support.


Contents
--------

.. toctree::
   :maxdepth: 2

   messages
   producers
   consumers
   handlers
   drivers

Installation & Examples
-----------------------

You should require the driver library of your choice with 
`composer <https://getcomposer.org/>`_ rather than ``pmg/queue`` directly. If
you're planning to use beanstalkd as your backend:

.. code-block:: sh

    composer require pmg/queue-pheanstalk:~1.0

See the core `examples directory <https://github.com/AgencyPMG/Queue/tree/master/examples>`_
on the `pheanstalk examples <https://github.com/AgencyPMG/queue-pheanstalk/tree/master/examples>`_
for some code samples on gluing everything together.


READ THIS: Glossary & Core Concepts
-----------------------------------

- A **message** is a serializable object that goes into the queue for later
  processing.
- A **producer** adds messages to the queue backend via a *driver* and a
  *router*.
- A **consumer** pulls messages out of the queue via *driver* and processes
  them via *handlers*.
- A **driver** is PHP representation of the queue backend. There is an in memory
  driver included in this library as an example (and for testing), and an
  implementation of a `beanstalkd <http://kr.github.io/beanstalkd/>`_ driver
  `available <https://github.com/AgencyPMG/queue-pheanstalk>`_.
- A **router** is used by a producer to look up the correct queue for a message.
- A **message handler** is used by the default *consumer* to actually do the work
  of processing a message
- An **envelope** is used internally to wrap up messages with retry information
  as well as metadata specific to drivers. Users need not worry about this
  unless they are implementing their own *driver*.
