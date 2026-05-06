#!/bin/bash

# Script to view docker container logs
set -e

# Determine docker-compose command
DOCKER_COMPOSE_CMD="docker-compose"
if ! command -v docker-compose &> /dev/null; then
    DOCKER_COMPOSE_CMD="docker compose"
fi

# Show logs with tail
echo "Displaying logs from all containers (press Ctrl+C to exit)..."
echo ""

$DOCKER_COMPOSE_CMD -f docker-compose.yml logs -f

