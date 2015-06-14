.PHONY: test testnocov simpleexample pheanstalkexample

testnocov:
	php vendor/bin/phpunit

test:
	php vendor/bin/phpunit --coverage-text

simpleexample:
	php examples/simple.php

pheanstalkexample:
	php examples/pheanstalk.php

examples: simpleexample pheanstalkexample

travis: test examples
