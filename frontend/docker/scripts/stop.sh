#!/bin/bash

# Script to stop frontend docker container
set -e

echo "🛑 Stopping Frontend Docker container..."

# Determine docker-compose command
DOCKER_COMPOSE_CMD="docker-compose"
if ! command -v docker-compose &> /dev/null; then
    DOCKER_COMPOSE_CMD="docker compose"
fi

# Stop container
$DOCKER_COMPOSE_CMD down

echo "✅ Frontend container stopped successfully!"

