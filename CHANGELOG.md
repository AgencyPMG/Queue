# Changelog

All notable changes to this project will be documented in this file.
This project adheres to [Semantic Versioning](http://semver.org/).

## 6.1.2

### Fixed

- `once` calls with no message handled will have an `{queueName} empty-receive`
  span name.

## 6.1.1

### Fixed

- Fixed the open telemetry `_register.php` file autoload

## 6.1.0

### Added

- Added open telemetry instrumentation around `Consumer::once` and
  `Driver::enqueue`. These respect [messaging span](https://opentelemetry.io/docs/specs/semconv/messaging/messaging-spans/)
  semantic conventions.

## 6.0.0

See [the upgrade guide](UPGRADE-6.0.md) for more information.

### Changed

- PHP 8.0+ is required
- Various interfaces have tightened return types

## 5.1.0

### Added

- Support PHP `^7.4 || ^8.0`

### Changed

- Dropped support for PHP 7.3

## 5.0.0

### Changed

- PHP 7.3+ is required.
- Implementing `PMG\Queue\Message` is no longer required! Messages passed
  to the the producer and handled by the consumer can be any PHP object now.
  In the cases where a plain object is used, the message *name* is the full
  qualified class name. However, you may implement `Message` should you desire to
  keep the old behavior of having a specific message name that differs from the
  FQCN.
- `PMG\Queue\Router::queueFor` now typehints against `object` instead of
  `Message`.
- All `PMG\Queue\MessageLifecycle` methods now typehint against `object`.
- `PMG\Queue\Envelope` and `DefaultEnvelope` now deal with `object` messages
  only and do not typehint again `Message`.
- `PMG\Queue\Driver::enqueue` now typehints agains `object` instead of message.
  if a driver gets an `Envelope` instance it should use that instead of creating
  its own envelope. See [`UPGRADE-5.0.md`](/UPGRADE-5.0.md) for more details.
- `PMG\Queue\MessageHandler::handle` now typehints against `object` instead of
  `Message`.
- `PMG\Queue\Producer::send` now typehints against `object` instead of `Message`.
- `MessageLifecycle::failed` no longer has an `$isRetrying` argument, instead
  `MessageLifecycyle::retrying` will be called instead.
- `PMG\Queue\Router::queueFor` now has a `?string` return type.
- Drivers should no longer call `Envelope::retry` instead, instead consumers
  should call this method along with any delay required from the `RetrySpec`.
  See `UPGRADE-5.0.md` for more details.

### Fixed

n/a

### Added

- A new `MessageLifecycle::retrying` method was added that gets called whenever
  a message fails and is retrying.
- `PMG\Queue\Lifecycle\DelegatingLifecycle` has a new named constructor:
  `fromIterable`. This uses PHP 7.1's `iterable` pseudo type.
- `RetrySpec::retryDelay` method added to allow a message to be delayed when
  retrying, if the driver supports it.
- `Envelope::retry` now accepts an `int $delay` to support delayed retries. Not all
  drivers will be able to support delaying.
- Similarly, `PMG\Queue\Driver` implementations must no longer call
  `Envelope::retry` as they were required to do previously. See `UPGRADE-5.0.md`
  for more details. Instead `PMG\Queue\Consumer` implementations should call
  `Envelope::retry`.

### Removed

- `PMG\Queue\NullLifecycle` was removed (deprecated in version 4.2), use
  `PMG\Queue\Lifecycle\NullLifecycle` instead.
- `PMG\Queue\Exception\MessageFailed` was removed (it was unused in the core).

### Deprecations

- `PMG\Queue\Lifecycle\DelegatingLifecycle::fromArray`. Use `fromIterable`
  instead.
- The `PMG\Queue\MessageTrait` has been deprecated. The behavior it provided (using
  the fully qualified class name as the message name) is now the default.

## 4.2.0

### Changed

- Deprecated `PMG\Queue\NullLifecycle`, use `PMG\Queue\Lifecycle\NullLifecycle`
  instead.

### Added

- Two new `PMG\Queue\MessageLifecycle` implementations:
    - `PMG\Queue\Lifecycle\DelegatingLifecycle` to delegate to one or more other
      message lifecycles
    - `PMG\Queue\Lifecycle\MappingLifecycle` to delegate to other message
      lifecycles based on the message name.

## 4.1.0

### Changed

- `PMG\Queue\Handler\Pcntl` was moved to `PMG\Queue\Handler\Pcntl\Pcntl`
- `Pcntl::wait` now returns a result object that provides the successful exit
  result as well as an exit code.

## 4.0.1

### Fixed

- Fixed a typo in `DefaultConsumer` causing undefined method errors. See
  https://github.com/AgencyPMG/Queue/pull/66

## 4.0.0 (Unreleased)

### Changed
- [BC Break] Dropped support for PHP 5.6
- [BC Break] `NativeSerializer` now uses a `PMG\Queue\Signer\Signer` to sign
  its message. Previously this was all handled by the serializer directly. See
  `UPGRADE-4.0.md` for a migration path.
- [BC Break, Internals] `AbstractPersistanceDriver::assureSerializer` was renamed
  to `ensureSerializer`.
- [BC Break, Internals] `Driver` now has more strict type declarations.
- [BC Break, Internals] `Driver::release` was introduced.
- [BC Break, Internals] `Consumer::once` and `Consumer::run` Now take an optional
  `MessageLifecycle` instance as their second argument. Only folks who wrote 
  custom consumer implementations need to worry about this. See `DefaultConsumer`
  for an example how this may be handled. End users can keep using the consumer
  exactly as they were.
- [BC Break, Internals] `MessageHandler::handle` now returns a promise object
  from the `guzzlehttp/promises` library. Only folks who wrote custom handler
  implementations need to worry about this.
- `RetrySpec::canRetry` now has a return type hint.
- `Consumer::once` and `Consumer::run` have `string` typehints for their
   `$queueName` arguments.
- `Consumer::stop` now has a typehint for its `$code` argument.

### Fixed
n/a

### Added
- A new `PMG\Queue\Signer\Signer` interface was added as a way to make the
  message signature generation and validation pluggable in `Serializer`
  implementations.
- `PMG\Queue\MessageLifecycle` was introduced as a way for you to hook into a
  consumer as it moves a message through its life.

## 3.2.0

### Changed

n/a

### Fixed

- PcntlForkingHandler now always exits, which prevents child processes from
  turning into extra consumers and forking child processes themselves. See
  https://github.com/AgencyPMG/Queue/pull/47

### Added

- Child processes that exit abnormaly in `PcntlForkingHandler` now throw an
  `AbnormalExit` exception with some info about what went wrong. Practically
  this has no impact: the job is still failed and (possibly) retried, but the
  thrown exception will be logged and hopefully give users a better place to
  start debugging.


## 3.1.0

### Changed

- `PcntlForkingHandler` now throws a `CouldNotFork` exception that causes the
  consumer to exit unsuccessfully. Since a failure to fork is clearly on level
  with a driver error -- a system issue, not an application issue -- this is
  more in line with what *should* happen. The consumer will exit and its process
  manager can restart it.

### Fixed
n/a

### Added

- There is a new `PMG\Queue\Handler\Pcntl` class that acts as a thin wrapper
  around the `pcntl_*` functions and `exit`. Mostly done for testing purposes.

## 3.0.0

### Changed

- [BC BREAK] PHP version requirement was bumped to 5.6
- [BC BREAK] `DefaultConsumer::once` (and the `Consumer` interface) have been
  changed to make `once` safe to run in a loop. In other words, it never throws
  exceptions unless it's a must stop or an exception thrown from a driver. All
  other exceptions are logged, but not fatal. The idea here is to make consumers
  safe to decorate without having to duplicate the error handling logic.
- [BC BREAK] `PMG\Queue\Serializer\SigningSerializer` has been merged into
  `NativeSerializer` and removed. Pass your key as the first argument to
  `NativeSerializer`'s constructor.
- [BC BREAK] `AbstractPersistanceDriver::getSerializer` was removed, use
  `AbstractPersistanceDriver::assureSerializer` instead.
- [BC BREAK] `Consumer::stop` now takes an optional exit code. Only really
  relevant for implementors or the `Consumer` interface.
- [BC BREAK] `MessageExecutor`, `HandlerResolver`, and their implementations
  have been removed. See `UPGRADE-3.0.md` for some info on migration.
- `Consumer` has docblocks that reflect its actual return values now.
- `PheanstalkDriver` is no longer part of the core. Instead of requiring
   `pmg/queue` directly in your `composer.json`, require `pmg/queue-pheanstalk`
   (or any other driver implementation).
- `DefaultConsumer` is no longer final, and its private methods are now
  protected.

### Fixed

- `DefaultConsumer` now catches and handles PHP 7's `Error` exceptions

### Added

- An `AbstractConsumer` class that provides the `run` and `stop` methods for
  consumers without tying them to a specific implementation of `once`.

## 2.1.0

This will be the last release in the 2.X series.

BC Breaks:

- None

Bug Fixes:

- `SigningSerializer` now uses `hash_equals` instead of strict equality. PHP 5.5
  users will fallback on the Symfony 5.6 polyfill.

New Features:

- Support for `allowed_classes` in PHP 7's `unserialize` function for
  `NativeSerializer`

## 2.0.2

Simple license update.

BC Breaks:

- None

Bug Fixes:

- None

## 2.0.1

BC Breaks:

- None

Bug Fixes:

- `SerializationError` is now treated as a `DriverError` since that's where
  those errors originate.

## 2.0.0

BC Breaks:

- everything, completely refactored

New Features:

- Multi queue support baked in
- Allow single and forking message execution
- Remove job options and shift them into drivers
