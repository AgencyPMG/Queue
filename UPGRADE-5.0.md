# Upgrade from 4.X to 5.X

## PHP Version Requirement Bumped to ~7.1

Stick with version 4.X should PHP 7.0 support be required.

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

    public function failed(Message $message, Consumer $consumer, bool $isRetrying)
    {
        // do failed thing
    }

    // ...
}
```
