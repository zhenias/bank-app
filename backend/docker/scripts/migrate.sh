#!/bin/bash

# Script to run database migrations
set -e

# Determine docker-compose command
DOCKER_COMPOSE_CMD="docker-compose"
if ! command -v docker-compose &> /dev/null; then
    DOCKER_COMPOSE_CMD="docker compose"
fi

echo "🔄 Running database migrations..."
echo ""

# Run migrations
$DOCKER_COMPOSE_CMD exec -T app php artisan migrate

echo ""
echo "✅ Migrations completed successfully!"

