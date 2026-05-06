#!/bin/bash

# Script to open shell in backend app container
set -e

# Determine docker-compose command
DOCKER_COMPOSE_CMD="docker-compose"
if ! command -v docker-compose &> /dev/null; then
    DOCKER_COMPOSE_CMD="docker compose"
fi

echo "🔓 Opening shell in backend app container..."
echo ""

$DOCKER_COMPOSE_CMD exec app /bin/bash

