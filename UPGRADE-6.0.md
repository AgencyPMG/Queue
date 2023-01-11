# Upgrade from 5.X to 6.X

## PHP Version Requirement Bumped to ^8.0

Stick with version 5.X should PHP ^7.3 support be required.

## Lifecycle Changes

- `PMG\Queue\Lifecycle\DelegatingLifecycle::fromArray` was removed, use
  `fromIterable` instead.
- `PMG\Queue\MessageLifecycle` now requires a `void` return type on all of its
  methods.

## Consumer Changes

- The `PMG\Queue\Consumer` interface now has return types on its methods.

## MessageTrait

- `PMG\Queue\MessageTrait` was removed, the default behavior has been to use the
  FQCN since 5.X, so this added no additional functionalty.
