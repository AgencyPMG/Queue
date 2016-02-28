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
- `Consumer` has docblocks that reflect its actual return values now.

### Fixed

n/a

### Added

- An `AbstractConsumer` class that provides the `run` and `stop` methods for
  consumers without tying them to a specific implementation of `once`.

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
