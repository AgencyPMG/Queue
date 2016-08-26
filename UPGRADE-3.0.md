# Upgrade from 2.X to 3.X

## PHP Version Requirement Bumped to ~5.6 or ~7.0

PHP 5.5+ was required in 2.X.

## Removal of Persistent Drivers

The biggest change is the extraction of the `PheanstalkDriver` into its own
library. Rather than `composer require pmg/queue`, you'll want to require the
pheanstalk driver instead: `composer require pmg/queue-pheanstalk`.

## Introduction of `MessageHandler` and removal of Exectors & Resolvers

Previously you gave the consumer an instance of a `MessageExector` that wrapped
up a `HandlerResolver`. The point of this was to map callables to message names.

In 3.0, we replace those two things with a single `MessageHandler` interface.
There are two built in: a `CallableHandler` and `PcntlForkingHandler`.

This was done to better reflect how PMG is using the Queue (and how we think it
should be used).

### 2.X

```php
use PMG\Queue as Q;

/** @var Q\Driver $driver */
$driver = createADriverSomehow();

$consumer = new Q\DefaultConsumer(
    $driver,
    new Q\Executor\SimpleExecutor(new Q\Resolver\MappingResolver([
        'AMessageName' => function (Q\Message $msg) {
          // do stuff
        },
        // other stuff here
    ]))
);
```

### 3.x

```php
use PMG\Queue as Q;

/** @var Q\Driver $driver */
$driver = createADriverSomehow();

$consumer = new Q\DefaultConsumer(
    $driver,
    new Q\Handler\CallableHandler(function (Q\Message $msg) {
        // see https://github.com/AgencyPMG/Queue/blob/master/examples
        // for some examples of what could be here.
    })
);
```

### But I Used the HandlerResolver!

No problem, it was moved into its own library.

```
composer require pmg/queue-mapping-handler
```

And use it like so:

```php
use PMG\Queue as Q;
use PMG\Queue\Handler\MappingHandler;

/** @var Q\Driver $driver */
$driver = createADriverSomehow();

$consumer = new Q\DefaultConsumer(
    $driver,
    MappingHandler::fromArray([
        'AMessageName' => function (Q\Message $msg) {
          // do stuff
        },
        // ...
    ])
);
```

## NativeSerializer & SigningSerializer Were Merged

`PMG\Queue\Serializer\NativeSerializer` now requires a key argument to its
constructor that it uses to sign messages.

### 2.X

```php
use PMG\Queue\Serializer\NativeSerializer;
use PMG\Queue\Serializer\SigningSerializer;

$serializer = new SigningSerializer(
    new NativeSerializer(),
    'your super secret key'
);

$driver = new WhateverDriver(/*...*/, $serializer);
```

### 3.X

```php
use PMG\Queue\Serializer\NativeSerializer;

$serializer = new NativeSerializer('your super secret key');

$driver = new WhateverDriver(/*...*/, $serializer);
```
