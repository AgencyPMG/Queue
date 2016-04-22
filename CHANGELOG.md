# Changelog

## 2.1.0 (unreleased)

BC Breaks:

- None

Bug Fixes:

- None

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
