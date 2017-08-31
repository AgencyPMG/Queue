# Upgrade from 3.X to 4.X

## PHP Version Requirement Bumped to ~7.0

Support for PHP 5.6+ dropped. Stick with 3.X need PHP 5 support is still
necessary.

## `NativeSerializer` Now Requires a `Signer` Instance

Previously one could pass a key to `NativeSerializer` directly. Now a `Signer`
instance is required. A named constructor is provided if the 3.X behavior is
still desired.

```php
use PMG\Queue\Signer\HmacSha256;
use PMG\Queue\Serializer\NativeSerializer;

// 3.X
$serializer = new NativeSerializer('secretKey');

// 4.X
$serializer = NativeSerializer::fromSigningKey('secretKey');
// $serializer = new NativeSerializer(new HmacSha256('secretKey'));
```

## Message Lifecycle in Consumers

`Consumer::once` and `Consumer::run` both take an optional `MessageLifecycle`
argument. This is a useful way to hook into the consumer as it moves a message
through its lifecycle (starting, completed, succeeded, failed). Users may
implement this themselves, see the [consumers documentation](http://pmg-queue.readthedocs.io/en/latest/consumers.html)
for more info.

## For Driver Authors

### `Driver` Has Stricter Type Declarations

Check the [`Driver` interface](https://github.com/AgencyPMG/Queue/blob/master/src/Driver.php)
for the updated method signatures.

### `Driver::release` was Added

This is a method that should skip the retry system for the given
envelope/message and put it back into a ready state immediately.

### `assureSerializer` was renamed in `AbstractPersistanceDriver`

```php
class SomeDriver implements Driver
{
    private function whatever()
    {
        // 3.X
        $this->assureSerializer()->serialize(...);

        // 4.X
        $this->ensureSerializer()->serialize(...);
    }
}
```

Prefer using `$this->serialize` or `$this->unserialize` instead of accessing the
serializer directly.
