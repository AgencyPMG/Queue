# Changelog


## 2.1.1

BC Breaks:

- None

Bug Fixes:

- Broadcast jobs get a higher priority as intended (but forgotten) in 2.1.0

New Features:

- None

## 2.1.0

BC Breaks:

- Added the `broadcast` method to `PMG\Queue\Driver` and `PMG\Queue\Producer`

Bug Fixes:

- None

New Features:

- Added the ability to *broadcast* messages to all queues.

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
