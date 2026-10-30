.DEFAULT_GOAL:=help
DOCKER_COMPOSE_SERVICE_NAME=php

.PHONY: help
help:
	@grep -E '^[\.0-9a-zA-Z_-]+:.*?## .*$$' Makefile | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'

.PHONY: setup
setup: remove-composer-lock phpLatest-composer-update ## Setup project and dependencies

.PHONY: remove-composer-lock
remove-composer-lock: ## Remove the composer.lock file
	rm -f composer.lock | true

.PHONY: all-test-fast
all-test-fast: remove-composer-lock phpLatest-composer-update phpLatest-test-fast ## Runs composer-update and test-fast for each configured php version

.PHONY: php-cs-fix
php-cs-fix: ## Run php-cs-fixer
	docker compose run --rm php sh -c "vendor/bin/php-cs-fixer fix"

#PHP Latest Section. Latest means latest available PHP version configured in docker-compose.yml
.PHONY: phpLatest-composer-install
phpLatest-composer-install: ## Run Composer install with PHP version Latest
	docker compose run --rm php sh -c "composer install"

.PHONY: phpLatest-composer-update
phpLatest-composer-update: ## Run Composer update with PHP version Latest
	docker compose run --rm php sh -c "composer update"

.PHONY: phpLatest-test-fast
phpLatest-test-fast: ## Run phpunit without coverage with PHP version Latest
	docker compose run --rm php sh -c "vendor/bin/phpunit --no-coverage"

.PHONY: phpLatest-test
phpLatest-test: ## Run phpunit with coverage with PHP version Latest
	docker compose run --rm php sh -c "vendor/bin/phpunit"

.PHONY: examples-ping
examples-ping: ## Runs the example code for the Ping API in the PHP version which is defined in DOCKER_COMPOSE_SERVICE_NAME
	docker compose run --rm ${DOCKER_COMPOSE_SERVICE_NAME} php examples/ping.php

.PHONY: examples-status
examples-status: ## Runs the example code for the status API in the PHP version which is defined in DOCKER_COMPOSE_SERVICE_NAME
	docker compose run --rm ${DOCKER_COMPOSE_SERVICE_NAME} php examples/status.php

.PHONY: examples-ship
examples-ship: ## Runs the example code for the Ship API in the PHP version which is defined in DOCKER_COMPOSE_SERVICE_NAME
	docker compose run --rm ${DOCKER_COMPOSE_SERVICE_NAME} php examples/ship.php
