# Executables (local)
DOCKER_COMP = docker compose

# Docker containers
PHP_CONT = $(DOCKER_COMP) exec php

# Executables
PHP      = $(PHP_CONT) php
COMPOSER = $(PHP_CONT) composer
SYMFONY  = $(PHP) bin/console

down:
	@$(DOCKER_COMP) -f compose.yaml -f compose.prod.yaml -f compose.dev.yaml down

build:
	@$(DOCKER_COMP) -f compose.yaml -f compose.prod.yaml build --no-cache

builddev:
	@$(DOCKER_COMP) -f compose.yaml -f compose.dev.yaml build --no-cache

up: ## Start the docker image in detached mode (no logs)
	@$(DOCKER_COMP) -f compose.yaml -f compose.prod.yaml up --detach --remove-orphans --force-recreate
	@$(SYMFONY) doctrine:database:create --if-not-exists
	@$(SYMFONY) doctrine:migrations:migrate --no-interaction

dev: ## Start the dev docker image in detached mode (no logs)
	@$(DOCKER_COMP) -f compose.yaml -f compose.dev.yaml up --detach --remove-orphans --force-recreate
	@$(SYMFONY) doctrine:database:create --if-not-exists
	@$(SYMFONY) doctrine:migrations:migrate --no-interaction
	@$(SYMFONY) doctrine:fixtures:load --no-interaction

test: ## Start tests
	@$(PHP_CONT) bash run-tests.sh