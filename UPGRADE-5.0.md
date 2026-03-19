# Upgrade from 4.X to 5.X

## PHP Version Requirement Bumped to ~7.3

Stick with version 4.X if PHP 7.0, 7.1, or 7.2 support is required.

## Messages No Longer Need to Implement `PMG\Queue\Message`

Producers and consumers can now deal with plain objects. By default, the
*message name* for plain object messages is the fully qualified class name
(FQCN).

You may, however, implement `PMG\Queue\Message` (and its `getName` method) should
you want to continue using message names other than FQCNs.

The `PMG\Queue\MessageTrait`, which provided FQCN-based naming, was also
removed.

### Router Updates for Message Names

The producer's routing configuration may need to be updated should you choose to
use FQCNs as the message names.

#### Version 4.X

```php
use PMG\Queue\Router\MappingRouter;

$router = new MappingRouter([
  'SomeMessage' => 'queueName',
]);
```

#### Version 5.x

```php
use Acme\QueueExample\SomeMessage;
use PMG\Queue\Router\MappingRouter;

$router = new MappingRouter([
   SomeMessage::class => 'queueName',
]);
```

## `MessageLifecycle` Has a New `retrying` Method

Instead of using an `$isRetrying` flag in the `failed` method, the consumer now
calls `retrying` when a message is being retried and `failed` otherwise.

This should be a pretty easy upgrade:

#### Version 4.X

```php
use PMG\Queue\Message;
use PMG\Queue\Consumer;
use PMG\Queue\Lifecycle\NullLifecycle;

class CustomLifecycle extends NullLifecycle
{
    public function failed(Message $message, Consumer $consumer, bool $isRetrying)
    {
        if ($isRetrying) {
            // do retrying thing
        } else {
            // do failed thing
        }
    }

    // ...
}
```

#### Version 5.X

```php
use PMG\Queue\Message;
use PMG\Queue\Consumer;
use PMG\Queue\Lifecycle\NullLifecycle;

class CustomLifecycle extends NullLifecycle
{
    public function retrying(object $message, Consumer $consumer)
    {
        // do retrying thing
    }

    public function failed(object $message, Consumer $consumer)
    {
        // do failed thing
    }

    // ...
}
```

## `Router::queueFor` Has a Return Type

Any custom implementations of `PMG\Queue\Router` will need to be updated.

#### Version 4.X

```php
use PMG\Queue\Message;
use PMG\Queue\Router;

final class CustomRouter implements Router
{
    public function queueFor(Message $message)
    {
        return '...';
    }
}
```

#### Version 5.X

```php
use PMG\Queue\Message;
use PMG\Queue\Router;

final class CustomRouter implements Router
{
    public function queueFor(Message $message) : ?string
    {
        return '...';
    }
}
```

## `MessageHandler::handle` Now Accepts an Object

Any custom implementation of `MessageHandler` will need to be updated.

```diff
 use GuzzleHttp\Promise\PromiseInterface;
 use PMG\Queue\MessageHandler;
-use PMG\Queue\Message;

 class SomeHandler implements MessageHandler
 {
-   public function handle(Message $message, array $options=[]) : PromiseInterface
+   public function handle(object $message, array $options=[]) : PromiseInterface
    {
        // ...
    }
 }
```


## Internals

All changes here are only relevant to authors of `PMG\Queue\Driver`,
`PMG\Queue\Consumer`, or `PMG\Queue\Producer` implementations.

### `Producer::send` Now Takes an `object` Instead of a `Message`

And `send` now has a `void` return type as well.

This is part of a broader change (see above) around `pmg/queue` handling plain
objects without requiring a `Message` implementation.

```diff
-use PMG\Queue\Message;
 use PMG\Queue\Producer;

 class SomeProducer implements Producer
 {

-   public function send(Message $message)
+   public function send(object $message) : void
    {
        // ...
    }
 }
```

### `Driver::enqueue` Now Takes an `object` Instead of a `Message`

Drivers should handle receiving an `Envelope` instance in this method as well.
If that happens, the driver *must* use that envelope instead of creating its
own.


```diff
 use PMG\Queue\Driver;
 use PMG\Queue\DefaultEnvelope;
-use PMG\Queue\Message;
 use PMG\Queue\Envelope;

 final class SomeDriver implements Driver
 {
     // ...

-    public function enqueue(string $queueName, Message $message) : Envelope
+    public function enqueue(string $queueName, object $message) : Envelope
     {
-       $e = new DefaultEnvelope($message);
+       $e = $message instanceof Envelope ? $message : new DefaultEnvelope($message);

        $this->queueUpTheMessageSomehow($queueName, $e);

        return $e;
     }
 }
```

### Drivers Should No Longer Call `Envelope::retry`

In 4.X (and lower) drivers were required to call `$envelope->retry()` on any
envelope passed in `Driver::retry`.

That should now happen in implementations of `PMG\Queue\Consumer` instead.

#### Version 4.X Driver

```php
use PMG\Queue\Driver;
use PMG\Queue\Envelope;

final class SomeDriver implements Driver
{
    // ...

    public function retry(string $queueName, Envelope $envelope) : Envelope
    {
        $e = $envelope->retry();
        $this->queueUpTheMessageSomehow($queueName, $e);

        return $e;
    }
}
```

#### Version 5.X Driver

```php
use PMG\Queue\Driver;
use PMG\Queue\Envelope;

final class SomeDriver implements Driver
{
    // ...

    public function retry(string $queueName, Envelope $envelope) : Envelope
    {
        $this->queueUpTheMessageSomehow($queueName, $envelope);

        return $envelope;
    }
}
```

#### Version 4.X Consumer

```php
use PMG\Queue\Consumer;
use PMG\Queue\MessageLifecycle;
use PMG\Queue\RetrySpec;

final class SomeConsumer implements Consumer
{
    /**
     * @var Driver
     */
    private $driver;

    /**
     * @var RetrySpec
     */
    private $retries;

    // ...

    public function once(string $queueName, MessageLifecycle $lifecycle=null)
    {
        $envelope = $this->driver->dequeue($queueName);
        if (!$envelope) {
            return null;
        }

        try {
            $this->processTheMessageSomehow($queueName, $envelope);
        } catch (\Exception $e) {
            if ($this->retries->canRetry($envelope)) {
                $this->driver->retry($envelope); // <-- No `$envelope->retry(...)`
            } else {
                $this->driver->fail($envelope);
            }
        }
    }
}
```

#### Version 5.X Consumer

```php
use PMG\Queue\Consumer;
use PMG\Queue\MessageLifecycle;
use PMG\Queue\RetrySpec;

final class SomeConsumer implements Consumer
{
    /**
     * @var Driver
     */
    private $driver;

    /**
     * @var RetrySpec
     */
    private $retries;

    // ...

    public function once(string $queueName, MessageLifecycle $lifecycle=null)
    {
        $envelope = $this->driver->dequeue($queueName);
        if (!$envelope) {
            return null;
        }

        try {
            $this->processTheMessageSomehow($queueName, $envelope);
        } catch (\Exception $e) {
            if ($this->retries->canRetry($envelope)) {
                $delay = $this->retries->retryDelay($envelope);
                $this->driver->retry($envelope->retry($delay)); // <-- Call `$envelope->retry(...)`
            } else {
                $this->driver->fail($envelope);
            }
        }
    }
}
```

### Drivers Have Stricter Return Types


`Driver::{enqueue,dequeue,retry}` all have an `Envelope` return type (or
`?Envelope` in the case of `dequeue`).

`Driver::{ack,fail,release}` all have a `void` return type.
