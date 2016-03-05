# Upgrade from 2.X to 3.X

## Removal of Persistent Drivers

The biggest change is the extraction of the `PheanstalkDriver` into its own
library. Rather than `composer require pmg/queue`, you'll want to require the
pheanstalk driver instead: `composer require pmg/queue-pheanstalk`.
