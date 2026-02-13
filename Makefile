.PHONY: help up down build test migrate fresh logs shell prod-up prod-down prod-logs prod-deploy

# Colors
GREEN  := \033[0;32m
YELLOW := \033[0;33m
BLUE   := \033[0;34m
NC     := \033[0m

help:
	@echo "$(GREEN)═══════════════════════════════════════════════════════════$(NC)"
	@echo "$(GREEN)  Shopify Integration - Makefile Commands$(NC)"
	@echo "$(GREEN)═══════════════════════════════════════════════════════════$(NC)"
	@echo ""
	@echo "$(BLUE)Development:$(NC)"
	@echo "  $(YELLOW)up$(NC)           - Start all containers"
	@echo "  $(YELLOW)down$(NC)         - Stop all containers"
	@echo "  $(YELLOW)restart$(NC)      - Restart all containers"
	@echo "  $(YELLOW)build$(NC)        - Build all containers"
	@echo "  $(YELLOW)rebuild$(NC)      - Rebuild and restart"
	@echo "  $(YELLOW)install$(NC)      - Install dependencies (backend + frontend)"
	@echo "  $(YELLOW)migrate$(NC)      - Run database migrations"
	@echo "  $(YELLOW)seed$(NC)         - Seed database with mock products"
	@echo "  $(YELLOW)fresh$(NC)        - Fresh install with seed data"
	@echo "  $(YELLOW)logs$(NC)         - View container logs"
	@echo "  $(YELLOW)shell$(NC)        - Open shell in backend container"
	@echo "  $(YELLOW)tinker$(NC)       - Open Laravel tinker"
	@echo ""
	@echo "$(BLUE)Testing:$(NC)"
	@echo "  $(YELLOW)test$(NC)         - Run all tests (backend + frontend)"
	@echo "  $(YELLOW)test-unit$(NC)    - Run unit tests only"
	@echo "  $(YELLOW)test-coverage$(NC) - Run tests with coverage"
	@echo ""
	@echo "$(BLUE)Code Quality:$(NC)"
	@echo "  $(YELLOW)lint$(NC)         - Run linters (Pint + ESLint)"
	@echo "  $(YELLOW)analyse$(NC)      - Run PHPStan analysis"
	@echo "  $(YELLOW)quality$(NC)      - Run all quality checks"
	@echo ""
	@echo "$(BLUE)Production:$(NC)"
	@echo "  $(YELLOW)prod-up$(NC)      - Start production containers"
	@echo "  $(YELLOW)prod-down$(NC)    - Stop production containers"
	@echo "  $(YELLOW)prod-logs$(NC)    - View production logs"
	@echo "  $(YELLOW)prod-deploy$(NC)  - Deploy latest changes"
	@echo "  $(YELLOW)prod-backup$(NC)  - Backup production database"
	@echo ""

up:
	docker-compose up -d

down:
	docker-compose down

build:
	docker-compose build

restart:
	@echo "$(YELLOW)Restarting containers...$(NC)"
	docker compose restart
	@echo "$(GREEN)Containers restarted!$(NC)"

install:
	@echo "$(GREEN)Installing dependencies...$(NC)"
	docker compose exec backend composer install
	docker compose exec frontend npm install
	@echo "$(GREEN)Dependencies installed!$(NC)"

test:
	@echo "$(GREEN)Running all tests...$(NC)"
	docker compose exec backend php artisan test
	docker compose exec frontend npm run test:unit -- --run

test-unit:
	@echo "$(GREEN)Running unit tests...$(NC)"
	docker compose exec backend php artisan test --testsuite=Unit

test-coverage:
	@echo "$(GREEN)Running tests with coverage...$(NC)"
	docker compose exec backend php artisan test --coverage --min=80

migrate:
	@echo "$(GREEN)Running migrations...$(NC)"
	docker compose exec backend php artisan migrate
	@echo "$(GREEN)Migrations completed!$(NC)"

seed:
	@echo "$(GREEN)Seeding database with mock products...$(NC)"
	docker compose exec backend php artisan db:seed
	@echo "$(GREEN)Database seeded!$(NC)"

fresh:
	@echo "$(YELLOW)Fresh install (this will delete all data)...$(NC)"
	docker compose down -v
	docker compose build
	docker compose up -d
	@echo "$(BLUE)Waiting for services to start...$(NC)"
	sleep 10
	docker compose exec backend php artisan migrate
	@echo "$(GREEN)Fresh install completed!$(NC)"

rebuild:
	@echo "$(YELLOW)Rebuilding containers...$(NC)"
	docker compose build
	docker compose restart
	@echo "$(GREEN)Rebuild completed!$(NC)"

logs:
	docker compose logs -f

shell:
	docker compose exec backend sh

tinker:
	docker compose exec backend php artisan tinker

lint:
	@echo "$(GREEN)Running linters...$(NC)"
	docker compose exec backend composer lint
	docker compose exec frontend npm run lint

analyse:
	@echo "$(GREEN)Running static analysis...$(NC)"
	docker compose exec backend composer analyse

quality: lint analyse test
	@echo "$(GREEN)All quality checks passed!$(NC)"

setup: up install migrate seed
	@echo "$(GREEN)═══════════════════════════════════════════════════════$(NC)"
	@echo "$(GREEN)  Setup completed! Access the app at:$(NC)"
	@echo "$(BLUE)  Frontend: http://localhost:3000$(NC)"
	@echo "$(BLUE)  Backend:  http://localhost/api$(NC)"
	@echo "$(GREEN)═══════════════════════════════════════════════════════=$(NC)"

# Production commands
prod-up:
	docker-compose -f docker-compose.prod.yml up -d

prod-down:
	docker-compose -f docker-compose.prod.yml down

prod-logs:
	docker-compose -f docker-compose.prod.yml logs -f

prod-deploy:
	git pull origin main
	docker-compose -f docker-compose.prod.yml pull
	docker-compose -f docker-compose.prod.yml up -d
	docker-compose -f docker-compose.prod.yml exec -T backend php artisan migrate --force
	docker-compose -f docker-compose.prod.yml exec -T backend php artisan config:cache
	docker-compose -f docker-compose.prod.yml exec -T backend php artisan route:cache
	docker-compose -f docker-compose.prod.yml restart queue-worker

prod-backup:
	./scripts/backup-database.sh

prod-cache-clear:
	docker-compose -f docker-compose.prod.yml exec backend php artisan cache:clear
	docker-compose -f docker-compose.prod.yml exec backend php artisan config:clear
	docker-compose -f docker-compose.prod.yml exec backend php artisan route:clear
