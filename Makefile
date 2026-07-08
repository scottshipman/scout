.PHONY: up down build restart logs sh console composer status

up:
	docker compose up -d --build

down:
	docker compose down

build:
	docker compose build --no-cache

restart:
	docker compose restart

logs:
	docker compose logs -f

sh:
	docker compose exec php bash

console:
	docker compose exec php php bin/console $(filter-out $@,$(MAKECMDGOALS))

composer:
	docker compose exec php composer $(filter-out $@,$(MAKECMDGOALS))

status:
	docker compose ps
