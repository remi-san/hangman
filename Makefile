FEATURES ?=

# install
install:
	composer install --prefer-dist

# remove all dependencies
remove-dependencies:
	rm -Rf vendor

# Remove dependencies and install
clean-install: remove-dependencies install

# Launch tests
test:
	./vendor/bin/phpunit

# Launch tests with coverage
test-coverage:
	./vendor/bin/phpunit -c phpunit-coverage.xml --coverage-text
