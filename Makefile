all: test

test:
	vendor/bin/phpunit tests/ --display-warnings --display-notices --log-events-text /dev/stdout

