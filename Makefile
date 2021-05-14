.PHONY: test


test: psalm phpunit
	@printf "\n\n\033[0;32mAll tests passed, you are ready to push commits\033[0m"

phpunit: export XDEBUG_MODE = coverage
phpunit:
	@vendor/bin/phpunit \
		--testdox -c . ${TEST_ARGS}

psalm:
	@vendor/bin/psalm
