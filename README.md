[![Build Status](https://travis-ci.org/AgencyPMG/Queue.svg?branch=master)](https://travis-ci.org/AgencyPMG/Queue)

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

## Examples?

See the [`examples`](https://github.com/AgencyPMG/Queue/tree/master/examples)
directory.

## Messages

Messages are serializable object that implement `PMG\Queue\Message`. The
interface only contains a single method `getName`.

Uses can can include the `PMG\Queue\MessageTrait` in their classes which simply
returns the class name from `getName`.

```php
class MyMessage implements \PMG\Queue\Message
{
    use \PMG\Queue\MessageTrait;

    // ...
}
```

Messages should include all the relevant properties for the *handler* to do its
work later. For instance, if you're sending a user some sort of notification, it
might be good to include the user's identifier in your message.

```php
class SendAlert implemetns \PMG\Queue\Message
{
    use \PMG\Queue\MessageTrait;

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

The default producer sends messages via a *driver* and a *router*.

## Routers

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

## Consumers

Consumers pull messages out a queue backend via a *driver* and handle them. The
default `PMG\Queue\Consumer` implementation accomplishes that with an
*hander resolvers*, *handlers*, and *executors*.

## Handlers & Resolvers

Handlers are callables that take a single argument: the message put into the
queue. When a message comes out of the queue, it's match to its hander via a
*handler resolver*.

### Matching a Handler Based on Name

`MappingResolver` matches messages (via their name) to handlers.

```php
use PMG\Queue\Resolver\MappingResolver;

$resolver = new MappingResolver([
    // The `SendAlert` class from above
    SendAlert::class => function (SendAlert $message) {
        // this is called by the consumer
    },
]);
```

### Using a Single Handler for Everything

`SimpleResolver` always returns the same handler for every job. This is useful
if you're doing something like sending messages from the queue through a
[command bus](http://tactician.thephpleague.com/).

```php
use PMG\Queue\Message;
use PMG\Queue\Resolver\SimpleResolver;

$resolver = new SimpleResolver(function (Message $message) {
    // called for everything message
});
```

## Executors

Handler resolver are used with executors to handle messages. Executors seem like
a bit of a ridiculous concept -- `SimpleExecutor` is just a wrapper around
`call_user_func` -- but its helpful to pull out the code that runs the handler
callbacks so it can be swapped out with something more complex (like
`ForkingExecutor`). All executor implement `PMG\Queue\MessageExecutor`.

### Handling the Message in the Same Thread

`SimpleExecutor` just calls the handler, if found, with `call_user_func` in the
same thread.

```php
use PMG\Queue\DefaultConsumer;
use PMG\Queue\Message;
use PMG\Queue\Executor\SimpleExecutor;
use PMG\Queue\Resolver\SimpleResolver;

$resolver = new SimpleResolver(function (Message $message) {
    // called for everything message
});

$executor = new SimpleExecutor($resolver);

// $driver instanceof PMG\Queue\Driver
$consumer = new DefaultConsumer($driver, $executor);
```

### Handling Messages with a Fork

`ForkingExecutor` calls `pcntl_fork` and runs the handler in a child thread.
This is useful if your handlers can eat a lot of memory or are otherwise
resource intensive and you want to clean things up completely. `ForkingExecutor`
takes a second argument, a callable, that throw execeptions are passed into.
Keep in mind that this happens in a child thread, so resources like files
and database connections may no longer be available. The default error callback
invokes [`error_log`](http://php.net/manual/en/function.error-log.php).


```php
use PMG\Queue\DefaultConsumer;
use PMG\Queue\Message;
use PMG\Queue\Executor\ForkingExecutor;
use PMG\Queue\Resolver\SimpleResolver;

$resolver = new SimpleResolver(function (Message $message) {
    // called for everything message
});

$executor = new ForkingExecutor($resolver, function (\Exception $e) {
    $logger = createYourLoggerSomehow();
    $logger->critical(/* log exception somehow */);
});

// $driver instanceof PMG\Queue\Driver
$consumer = new DefaultConsumer($driver, $executor);
```

## Drivers

Drivers are the queue backend hidden behind the `PMG\Queue\Driver` interface.
`pmg/queue` comes with two drivers built in: *memory* and *pheanstalk*
(beanstalkd).

### The Memory Driver

The memory driver is provided to make prototyping easy. It uses `SplQueue`
instances and only keep messages in memory.


```php
use PMG\Queue\DefaultConsumer;
use PMG\Queue\Driver\MemoryDriver;

// ...

$driver = new MemoryDriver();

// $executor instanceof PMG\Queue\MessageExecutor
$consumer = new DefaultConsumer($driver, $executor);
```

### Serializers and Persistent Backends

Queues drivers that persist longer than a single request (or script run) require
some sort of serialization of messages. That happens via `PMG\Queue\Serializer\Serializer`
implementations. By default, `PheanstalkDriver` will use use `PMG\Queue\Serializer\NativeSerializer`
which simply calls `serialize` and `unserialize` and runs the output `base64_encode`
and `base64_decode` respectively.

You can (and **should**) consider wrapping the native serializer with
`SignedSerializer` which will prepend an HMAC to the serialized message to help
verify integrity when unserializing.

### Using Beanstalkd and Pheanstalk

[Pheanstalk](https://github.com/pda/pheanstalk) is a PHP library for interacting
with [Beanstalkd](http://kr.github.io/beanstalkd/). `PheanstalkDriver` lets you
take advantage of Beanstalkd as a queue backend.


```php
use Pheanstalk\Pheanstalk;
use PMG\Queue\DefaultConsumer;
use PMG\Queue\Driver\PheanstalkDriver;
use PMG\Queue\Serializer\NativeSerializer;
use PMG\Queue\Serializer\SignignSerializer;

// ...

$serilizer = new SigningSerializer(
    new NativeSerializer(),
    'this is the secret key'
);

$driver = new PheanstalkDriver(new \Pheanstalk('localhost'), [
    // how long easy message has to execute in seconds
    'ttr'               => 100,

    // the "priority" of the message. High priority messages are
    // consumed first.
    'priority'          => 1024,

    // The delay between inserting the message and when it
    // becomes available for consumption
    'delay'             => 0,

    // The ttr for retries jobs
    'retry-ttr'         => 100,

    // the priority for retried jobs
    'retry-priority'    => 1024,

    // the delay for retried jobs
    'retry-delay'       => 0,

    // When jobs fail, they are "burieds" in beanstalkd with this priority
    'fail-priority'     => 1024,

    // A call to `dequeue` blocks for this number of seconds. A zero or
    // falsy value will block until a job becomes available
    'reserve-timeout'   => 10,
], $serializer);

// $executor instanceof PMG\Queue\MessageExecutor
$consumer = new DefaultConsumer($driver, $executor);
```

## Retrying Failed Messages

*Consumers* will attempt to handle a message 5 times by default. This is defined
by a `RetrySpec`, which is passed as the third argument to consumers.


### Limiting Attemps

`LimitedSpec` will limit the attempts on a message to the value passed into
the constructor.

```php
use PMG\Queue\DefaultConsumer;
use PMG\Queue\Retry\LimitedSpec;

// allow a single retry
$retry = new LimitedSpec(1);

// $driver instanceof PMG\Queue\Driver
// $executor instanceof PMG\Queue\MessageExecutor
$consumer = new DefaultConsumer($driver, $executor, $retry);
```

### Never Retry a Message

`NeverSpec` will not allow retries of a message.

```php
use PMG\Queue\DefaultConsumer;
use PMG\Queue\Retry\NeverSpec;

// never retry, each job is only run once
$retry = new NeverSpec();

// $driver instanceof PMG\Queue\Driver
// $executor instanceof PMG\Queue\MessageExecutor
$consumer = new DefaultConsumer($driver, $executor, $retry);
```

## Logging

`DefaultConsumer` includes support for logging via PSR 3 logger
(`Psr\Log\LoggerInterface`). By default that's a `Psr\Log\NullLogger`, but any
other logger can be used by passing it in as the last argument to
`DefaultConsumer`'s constructor.

```php
use PMG\Queue\DefaultConsumer;
use PMG\Queue\Retry\NeverSpec;

$logger = getTheLoggerSomeHow();

// $driver instanceof PMG\Queue\Driver
// $executor instanceof PMG\Queue\MessageExecutor
// $retry instanceof PMG\Queue\RetrySpec
$consumer = new DefaultConsumer($driver, $executor, $retry, $logger);
```
