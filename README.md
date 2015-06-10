# PMG\Queue

A task queue for PHP.

## Glossary & Core Concepts

- A **queue** is a single bucket into which *messages* are put and fetched.
  PMG\Queue supports having multiple queues handled by a single consumer and
  producer.
- A **producer** puts *messages* into the queue.
- A **consumper** pulls *messages* out of the queue and acts on it.
- A **message** is serializable object that goes into the queue. Messages must
  implement `PMG\Queue\Message`, which is an empty marker interface.
- **Routers** connect messages with their appropriate queues.
- **Handlers** are used by consumers to execute messages. This are simple
  callables.
- A **handler resolver** locates handlers for messages.

## Messages

Messages are serializable PHP values that go into the queue. Only strings or
objects are allowed. The queue which a message goes in is determined by a router.
