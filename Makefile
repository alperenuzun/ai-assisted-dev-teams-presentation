.PHONY: help setup up down bash composer test pint migrate fixtures

help:
	@echo "Available commands:"
	@echo "  make setup    - Initial project setup (composer install, database)"
	@echo "  make up       - Start Docker containers"
	@echo "  make down     - Stop Docker containers"
	@echo "  make bash     - Access PHP container shell"
	@echo "  make composer - Run composer commands (e.g., make composer ARGS='require package')"
	@echo "  make test     - Run Pest tests"
	@echo "  make pint     - Run Laravel Pint code style check"
	@echo "  make migrate  - Run database migrations"
	@echo "  make fixtures - Load database fixtures"

setup: up
	docker exec blog-php composer install
	docker exec blog-php php bin/console lexik:jwt:generate-keypair --skip-if-exists
	docker exec blog-php php bin/console doctrine:database:create --if-not-exists
	docker exec blog-php php bin/console doctrine:migrations:migrate --no-interaction
	docker exec blog-php php bin/console doctrine:fixtures:load --no-interaction

up:
	docker-compose up -d

down:
	docker-compose down

bash:
	docker exec -it blog-php sh

composer:
	docker exec blog-php composer $(ARGS)

test:
	docker exec blog-php vendor/bin/pest

pint:
	docker exec blog-php vendor/bin/pint --test

migrate:
	docker exec blog-php php bin/console doctrine:migrations:migrate --no-interaction

fixtures:
	docker exec blog-php php bin/console doctrine:fixtures:load --no-interaction
