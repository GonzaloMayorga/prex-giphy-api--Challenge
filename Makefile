SHELL := /bin/bash

COMPOSE := docker compose
APP := $(COMPOSE) exec app

.PHONY: \
	build up down restart ps logs shell \
	composer-install key migrate seed fresh \
	passport-keys passport-client \
	test lint format analyse audit quality routes \
	diagrams

build:
	$(COMPOSE) build

up:
	$(COMPOSE) up -d

down:
	$(COMPOSE) down

restart:
	$(COMPOSE) down
	$(COMPOSE) up -d

ps:
	$(COMPOSE) ps

logs:
	$(COMPOSE) logs -f

shell:
	$(APP) bash

composer-install:
	$(APP) composer install

key:
	$(APP) php artisan key:generate

migrate:
	$(APP) php artisan migrate

seed:
	$(APP) php artisan db:seed

fresh:
	$(APP) php artisan migrate:fresh --seed

passport-keys:
	$(APP) php artisan passport:keys --force

passport-client:
	$(APP) php artisan passport:client \
		--personal \
		--provider=users \
		--name="Prex Giphy API Personal Access Client"

test:
	$(APP) php artisan test

lint:
	$(APP) composer lint

format:
	$(APP) composer format

analyse:
	$(APP) composer analyse

audit:
	$(APP) composer security

quality:
	$(APP) composer quality

routes:
	$(APP) php artisan route:list --path=api -vv

diagrams:
	./scripts/render-diagrams.sh
