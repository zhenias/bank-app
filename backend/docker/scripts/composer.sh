#!/bin/bash

# Script to run Composer commands
set -e

# Determine docker-compose command
DOCKER_COMPOSE_CMD="docker-compose"
if ! command -v docker-compose &> /dev/null; then
    DOCKER_COMPOSE_CMD="docker compose"
fi

# Check if command was provided
if [ -z "$1" ]; then
    echo "❌ Usage: bash docker/scripts/composer.sh <command>"
    echo ""
    echo "Examples:"
    echo "  bash docker/scripts/composer.sh install"
    echo "  bash docker/scripts/composer.sh require laravel/debugbar"
    echo "  bash docker/scripts/composer.sh update"
    exit 1
fi

echo "📦 Running: composer $@"
echo ""

# Run composer command
$DOCKER_COMPOSE_CMD exec -T app composer "$@"

