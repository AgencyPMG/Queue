.PHONY: test testnocov simpleexample retryexample

testnocov:
	php vendor/bin/phpunit

test:
	php vendor/bin/phpunit --coverage-text

simpleexample:
	php examples/simple.php

retryexample:
	php examples/retrying.php

examples: simpleexample retryexample

travis: test examples
