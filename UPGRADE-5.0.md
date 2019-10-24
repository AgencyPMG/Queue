# Upgrade from 4.X to 5.X

## PHP Version Requirement Bumped to ~7.2

Stick with version 4.X should PHP 7.0 or 7.1 support be required.

## No More `Message` Names by Default

Previously the `PMG\Queue\Message` interface required a `getName` method. It
no longer does. Instead the names default to the fully qualified class name
(FQCN) of the message.

Should the old behavior still be desired, implement the `PMG\Queue\NamedMessage`
interface which still includes the `getName` method.

The `PMG\Queue\MessageTrait` which provided the FQCN as a name behavior was also
removed.

#### Version 4.X (with FQCN as Message Name)

```php

namespace Acme\QueueExample;

use PMG\Queue\Message;

final class SomeMessage implements Message
{
    public function getName()
    {
        return __CLASS__;
    }

    // ...
}
```

#### Version 5.X (with FQCN as Message Name)

```php

namespace Acme\QueueExample;

use PMG\Queue\Message;

final class SomeMessage implements Message
{
    // message name is now `Acme\QueueExample\SomeMessage`
    // ...
}
```

#### Version 4.X (with Custom Message Name)

```php

namespace Acme\QueueExample;

use PMG\Queue\Message;

final class SomeMessage implements Message
{
    public function getName()
    {
        return 'SomeMessage';
    }

    // ...
}
```

#### Version 5.X (with Custom Message Name)

```php

namespace Acme\QueueExample;

use PMG\Queue\NamedMessage;

final class SomeMessage implements NamedMessage
{
    public function getName()
    {
        return 'SomeMessage';
    }

    // ...
}
```

### Router Updates for Message Names

Additionally, the producers routing configuration may need to be updated.

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

Rather than have an `$isRetrying` flag in the `failed` method. If the message is
being retried the `retrying` method will be invoked, otherwise `failed` will.

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
    public function retrying(Message $message, Consumer $consumer)
    {
        // do retrying thing
    }

    public function failed(Message $message, Consumer $consumer)
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

## Internals

All changes here are only relevant to authors of `PMG\Queue\Driver`,
`PMG\Queue\Consumer`, or `PMG\Queue\Producer` implementations.

### `Driver::enqueue` Now Takes an `object` instead of a `Message`

Drivers should handle receiving an `Envelope` instance in this method as well.
Should that happen the driver *must* use that envelope instead of creating its
own.

#### Version 4.X Driver

```php
use PMG\Queue\Driver;
use PMG\Queue\DefaultEnvelope;
use PMG\Queue\Envelope;

final class SomeDriver implements Driver
{
    // ...

    public function enqueue(string $queueName, Message $message) : Envelope
    {
        $e = new DefaultEnvelope($message);

        $this->queueUpTheMessageSomehow($queueName, $e);

        return $e;
    }
}
```

#### Version 5.X Driver

```php
use PMG\Queue\Driver;
use PMG\Queue\DefaultEnvelope;
use PMG\Queue\Envelope;

final class SomeDriver implements Driver
{
    // ...

    public function enqueue(string $queueName, object $message) : Envelope
    {
        $e = $message instanceof Envelope ? $message : new DefaultEnvelope($message);

        $this->queueUpTheMessageSomehow($queueName, $e);

        return $e;
    }
}
```


### Drivers Should No Longer Call `Envelope::retry`

In 4.X (and lower) drivers were required to call `$envelope->retry()` on any
envelope passed in `Driver::retry`.

That should now happen in implements of `PMG\Queue\Consumer` instead.

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

    public function retry(string $queueName, Envelope $envelope) : void
    {
        $this->queueUpTheMessageSomehow($queueName, $envelope);
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
            $this->processTheMessageSomehow($
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
            $this->processTheMessageSomehow($
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
