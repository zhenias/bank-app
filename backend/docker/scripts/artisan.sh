#!/bin/bash

# Script to run Laravel Artisan commands
set -e

# Determine docker-compose command
DOCKER_COMPOSE_CMD="docker-compose"
if ! command -v docker-compose &> /dev/null; then
    DOCKER_COMPOSE_CMD="docker compose"
fi

# Check if command was provided
if [ -z "$1" ]; then
    echo "❌ Usage: bash docker/scripts/artisan.sh <command>"
    echo ""
    echo "Examples:"
    echo "  bash docker/scripts/artisan.sh migrate"
    echo "  bash docker/scripts/artisan.sh tinker"
    echo "  bash docker/scripts/artisan.sh make:model Post"
    exit 1
fi

echo "📋 Running: php artisan $@"
echo ""

# Run artisan command
$DOCKER_COMPOSE_CMD exec -T app php artisan "$@"

