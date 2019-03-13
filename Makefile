dockerfile = ci/test/php$(PHP_VERSION)/Dockerfile
image      = events-test:php$(PHP_VERSION)

workdir = ${shell pwd}
uid     = ${shell id -u}

test-install: check-PHP_VERSION
	docker build -t $(image) --build-arg uid=$(uid) -f $(dockerfile) .
	docker run --rm -v $(workdir):/app -e GITHUB_TOKEN $(image) composer-install

test-run: check-PHP_VERSION
ifdef CODE_COVERAGE
	docker run --rm -v $(workdir):/app $(image) /app/vendor/bin/phpunit --coverage-clover clover.xml
else
	docker run --rm -v $(workdir):/app $(image) /app/vendor/bin/phpunit
endif

check-%:
	${if $($(*)), , ${error $(*) undefined}}
