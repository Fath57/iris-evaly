# Evaly Docker Makefile
.PHONY: help build up down restart logs shell mysql redis fresh seed test npm-install npm-build

# Default target
help:
	@echo "Evaly Docker Commands:"
	@echo "  make build        - Build Docker images"
	@echo "  make up           - Start all containers"
	@echo "  make down         - Stop all containers"
	@echo "  make restart      - Restart all containers"
	@echo "  make logs         - View container logs"
	@echo "  make shell        - Access app container shell"
	@echo "  make mysql        - Access MySQL CLI"
	@echo "  make redis        - Access Redis CLI"
	@echo "  make fresh        - Fresh migration with seeders"
	@echo "  make seed         - Run database seeders"
	@echo "  make test         - Run tests"
	@echo "  make npm-install  - Install npm dependencies"
	@echo "  make npm-build    - Build frontend assets"
	@echo "  make setup        - Initial setup (build, up, install, migrate)"

# Build images
build:
	docker compose build

# Start containers
up:
	docker compose up -d

# Stop containers
down:
	docker compose down

# Restart containers
restart:
	docker compose restart

# View logs
logs:
	docker compose logs -f

# Access app shell
shell:
	docker compose exec app bash

# Access MySQL
mysql:
	docker compose exec mysql mysql -u evaly -psecret evaly

# Access Redis
redis:
	docker compose exec redis redis-cli

# Fresh migration
fresh:
	docker compose exec app php artisan migrate:fresh --seed

# Run seeders
seed:
	docker compose exec app php artisan db:seed

# Run tests
test:
	docker compose exec app php artisan test

# Install npm dependencies
npm-install:
	docker compose run --rm node npm install

# Build frontend
npm-build:
	docker compose run --rm node npm run build

# Initial setup
setup:
	@echo "Building Docker images..."
	docker compose build
	@echo "Starting containers..."
	docker compose up -d
	@echo "Waiting for MySQL to be ready..."
	sleep 10
	@echo "Generating application key..."
	docker compose exec app php artisan key:generate
	@echo "Running migrations..."
	docker compose exec app php artisan migrate --seed
	@echo "Building frontend assets..."
	docker compose run --rm node sh -c "npm install && npm run build"
	@echo "Creating storage link..."
	docker compose exec app php artisan storage:link
	@echo ""
	@echo "Setup complete! Access the app at http://localhost:8007"

# Artisan command helper
artisan:
	docker compose exec app php artisan $(filter-out $@,$(MAKECMDGOALS))

# Composer command helper
composer:
	docker compose exec app composer $(filter-out $@,$(MAKECMDGOALS))

# Clear all caches
cache-clear:
	docker compose exec app php artisan cache:clear
	docker compose exec app php artisan config:clear
	docker compose exec app php artisan route:clear
	docker compose exec app php artisan view:clear

# Optimize for production
optimize:
	docker compose exec app php artisan config:cache
	docker compose exec app php artisan route:cache
	docker compose exec app php artisan view:cache

%:
	@:
