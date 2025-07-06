DOCKER_COMP = docker compose
PHP_CONT = $(DOCKER_COMP) exec php
COMPOSER = $(PHP_CONT) composer

.PHONY: start stop build composer bash test

start:
	docker compose up --wait

stop:
	docker compose down --remove-orphans

build:
	docker compose build --pull --no-cache

test:
	docker-compose exec php ./bin/phpunit

bash: ## Connect to the FrankenPHP container via bash so up and down arrows go to previous commands
	@$(PHP_CONT) bash

composer: ## Run composer, pass the parameter "c=" to run a given command, example: make composer c='req symfony/orm-pack'
	@$(eval c ?=)
	@$(COMPOSER) $(c)
