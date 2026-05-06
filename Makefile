.PHONY: help start stop restart logs shell db-reset build clean

# Default target
help:
	@echo "🚀 Bank App Docker Commands"
	@echo ""
	@echo "Usage: make [target]"
	@echo ""
	@echo "Main targets:"
	@echo "  start          Start all docker services"
	@echo "  stop           Stop all docker services"
	@echo "  restart        Restart all docker services"
	@echo "  logs           View logs from all containers"
	@echo "  build          Build docker images"
	@echo ""
	@echo "Backend targets:"
	@echo "  backend-shell  Open shell in backend container"
	@echo "  migrate        Run database migrations"
	@echo "  fresh          Fresh migrations (reset database)"
	@echo "  tinker         Open Laravel tinker shell"
	@echo "  artisan        Run artisan command (make artisan CMD='your-command')"
	@echo "  composer       Run composer command (make composer CMD='your-command')"
	@echo ""
	@echo "Frontend targets:"
	@echo "  frontend-shell Open shell in frontend container"
	@echo "  npm            Run npm command (make npm CMD='your-command')"
	@echo ""
	@echo "Database targets:"
	@echo "  db-shell       Open MySQL shell in mariadb container"
	@echo ""
	@echo "Utility targets:"
	@echo "  clean          Remove all containers, volumes and images"
	@echo "  help           Show this help message"

# Main commands
start:
	@bash docker/scripts/start.sh

stop:
	@bash docker/scripts/stop.sh

restart: stop start

logs:
	@bash docker/scripts/logs.sh

build:
	docker-compose build

# Backend commands
backend-shell:
	@bash backend/docker/scripts/shell.sh

migrate:
	@bash backend/docker/scripts/migrate.sh

fresh:
	@bash backend/docker/scripts/db-fresh.sh

tinker:
	@bash backend/docker/scripts/artisan.sh tinker

artisan:
	@bash backend/docker/scripts/artisan.sh $(CMD)

composer:
	@bash backend/docker/scripts/composer.sh $(CMD)

# Frontend commands
frontend-shell:
	@bash frontend/docker/scripts/shell.sh

npm:
	@bash frontend/docker/scripts/npm.sh $(CMD)

# Database commands
db-shell:
	docker-compose exec mariadb mysql -u laravel -plaravel_password backend

# Cleanup
clean:
	@echo "🧹 Cleaning up Docker..."
	docker-compose down -v --rmi all
	@echo "✅ Cleanup complete!"

# Development database reset with confirmation
db-reset:
	@bash docker/scripts/db-reset.sh

