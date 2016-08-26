# Changelog

All notable changes to this project will be documented in this file.
This project adheres to [Semantic Versioning](http://semver.org/).

## 3.0.0 (Unreleased)

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
