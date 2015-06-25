.PHONY: test testnocov simpleexample pheanstalkexample retryexample

testnocov:
	php vendor/bin/phpunit

test:
	php vendor/bin/phpunit --coverage-text

simpleexample:
	php examples/simple.php

pheanstalkexample:
	php examples/pheanstalk.php

retryexample:
	php examples/retrying.php

broadcastexample:
	php examples/broadcast.php

examples: simpleexample pheanstalkexample retryexample broadcastexample

travis: test examples
