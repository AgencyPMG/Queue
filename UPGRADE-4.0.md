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
