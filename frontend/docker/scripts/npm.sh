#!/bin/bash

# Script to run npm commands
set -e

# Determine docker-compose command
DOCKER_COMPOSE_CMD="docker-compose"
if ! command -v docker-compose &> /dev/null; then
    DOCKER_COMPOSE_CMD="docker compose"
fi

# Check if command was provided
if [ -z "$1" ]; then
    echo "❌ Usage: bash docker/scripts/npm.sh <command>"
    echo ""
    echo "Examples:"
    echo "  bash docker/scripts/npm.sh install"
    echo "  bash docker/scripts/npm.sh run build"
    echo "  bash docker/scripts/npm.sh install package-name"
    exit 1
fi

echo "📦 Running: npm $@"
echo ""

# Run npm command
$DOCKER_COMPOSE_CMD exec -T app npm "$@"

