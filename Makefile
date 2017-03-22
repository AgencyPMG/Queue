.PHONY: test testnocov simpleexample retryexample

testnocov:
	php vendor/bin/phpunit -v

test:
	php vendor/bin/phpunit --coverage-text -v

simpleexample:
	php examples/simple.php

retryexample:
	php examples/retrying.php

examples: simpleexample retryexample

travis: test examples
