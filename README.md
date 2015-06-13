# PMG\Queue

`pmg/queue` is a production ready queue framework that powers many internal
projects at [PMG](https://www.pmg.com/).

It's simple and extensible a number of features we've found to be the most
useful including automatic retries and multi-queue support.

## Glossary & Core Concepts

- A **message** is a serializable object that goes into the queue for later
  processing.
- A **producer** adds messages to the queue backend via a *driver* and a
  *router*.
- A **consumer** pulls messages out of the queue via *driver* and executes them
  with *handlers* and *executors*.
- A **driver** is PHP representation of the queue backend. There are two built
  in: memory and [beanstalkd](http://kr.github.io/beanstalkd/). Drivers
  implement `PMG\Queue\Driver`.
- A **router** looks up the correct queue name for a message based on its name.
- An **executor** runs the message *handler*. This is a simple abstraction to
  allow folks to fork and run jobs if they desire.
- A **handler** is a callable that does the work defined by a message.
- **handler resolvers** find handlers based on the *message* name.

## Example

## Messages

Messages are serializable object that implement `PMG\Queue\Message`. The
interface only contains a single method `getName`.

Uses can can include the `PMG\Queue\MessageTrait` in their classes which simply
returns the class name from `getName`.

```php
class MyMessage implements \PMG\Queue\Message
{
    use \PMG\Queue\Message;

    // ...
}
```

Messages should include all the relevant properties for the *handler* to do its
work later. For instance, if you're sending a user some sort of notification, it
might be good to include the user's identifier in your message.

```php
class SendAlert implemetns \PMG\Queue\Message
{
    use \PMG\Queue\Message;

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
```

`PMG\Queue\SimpleMessage` can be used in cases where an entire class doesn't
make sense. It takes a name and payload as arguments.

```php
$message = new \PMG\Queue\SimpleMessage('SendAlert', [
    'userId'    => 1,
]);
```

## Producers & Routers

Producers send messages into the queue backend via a driver. All producers
implement `PMG\Queue\Producer` which has a single method: `send`.

```php
$message = new SendAlert($userId);
/** @var PMG\Queue\Producer */
$queueProducer->send($message);
```

`pmg/queue` supports multiple queues and messages are routed to a queue via
implementations of `PMG\Queue\Router`.

### Using Only One Queue

For simple systems, only one queue may be needed. If so, use the `SimpleRouter`
which will always return the same queue name.

```php
use PMG\Queue\DefaultProducer;
use PMG\Queue\Router\SimpleRouter;

// $driver instanceof PMG\Queue\Driver
$producer = new DefaultProducer($driver, new SimpleRouter('QueueName'));
```

### Multiple Queues

`MappingRouter` maps message names to queue names, it takes an array or
`ArrayAccess` implementation as its only argument.

```php
use PMG\Queue\DefaultProducer;
use PMG\Queue\Router\MappingRouter;

// $driver instanceof PMG\Queue\Driver
$router = new MappingRouter([
    'SendAlert'   => 'QueueName1',
    'AnotherTask' => 'QueueName2',
]);
$producer = new DefaultProducer($driver, $router);
```

### Falling Back to a Default Queue

Producers will error if a queue name is not found. If your system requires a
fallback, wrap another router with `FallbackRouter` to ensure the default queue
is always used.

```php
use PMG\Queue\DefaultProducer;
use PMG\Queue\SimpleMessage;
use PMG\Queue\Router\FallbackRouter;
use PMG\Queue\Router\MappingRouter;

$router = new MappingRouter([
    'SendAlert'   => 'QueueName1',
    'AnotherTask' => 'QueueName2',
]);

// $driver instanceof PMG\Queue\Driver
$producer = new DefaultProducer($driver, new FallbackRouter($router, 'FallbackQueue'));

// `DoStuff` message goes into `FallbackQueue`.
$producer->send(new SimpleMessage('DoStuff'));
```
