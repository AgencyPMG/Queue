# Upgrade from 2.X to 3.X

## Removal of Persistent Drivers

The biggest change is the extraction of the `PheanstalkDriver` into its own
library. Rather than `composer require pmg/queue`, you'll want to require the
pheanstalk driver instead: `composer require pmg/queue-pheanstalk`.

## PHP Version Requirement Bumped to ~5.6 or ~7.0

PHP 5.5+ was required in 2.X.
