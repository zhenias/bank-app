#!/bin/bash

# Script to reset database
set -e

# Determine docker-compose command
DOCKER_COMPOSE_CMD="docker-compose"
if ! command -v docker-compose &> /dev/null; then
    DOCKER_COMPOSE_CMD="docker compose"
fi

echo "🔄 Resetting database..."
echo "⚠️  This will drop and recreate all tables!"
echo ""

# Reset database for main compose
$DOCKER_COMPOSE_CMD -f docker-compose.yml exec -T backend php artisan migrate:fresh --force

echo "✅ Database reset successfully!"

