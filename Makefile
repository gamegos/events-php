.PHONY: test test-php83 test-php84

test: check-PHP_VERSION ## Run tests in the default PHP version
	docker compose run --build --rm tester vendor/bin/phpunit \
		--coverage-text=php://stdout --coverage-html=.tmp/reports/coverage

test-php83: ## Run tests with PHP 8.3
	PHP_VERSION=8.3 make --no-print-directory test

test-php84: ## Run tests with PHP 8.4
	PHP_VERSION=8.4 make --no-print-directory test

check-%:
	${if $($(*)), , ${error $(*) undefined}}
