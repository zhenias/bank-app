#!/bin/bash

# Script to reset database (fresh migration)
set -e

# Determine docker-compose command
DOCKER_COMPOSE_CMD="docker-compose"
if ! command -v docker-compose &> /dev/null; then
    DOCKER_COMPOSE_CMD="docker compose"
fi

echo "🔄 Resetting database (migrate:fresh)..."
echo "⚠️  This will drop all tables and re-run migrations!"
echo ""

read -p "Are you sure? (yes/no): " -n 3 -r
echo
if [[ ! $REPLY =~ ^[Yy][Ee][Ss]$ ]]; then
    echo "Cancelled."
    exit 0
fi

# Run migrations reset
$DOCKER_COMPOSE_CMD exec -T app php artisan migrate:fresh --force

echo ""
echo "✅ Database reset successfully!"

