# Changelog

All notable changes to this project will be documented in this file.
This project adheres to [Semantic Versioning](http://semver.org/).

## 3.0.0 (Unreleased)

### Changed

- PHP version requirement was bumped to 5.6 (BC BREAK)

### Fixed

n/a

### Added

n/a

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
