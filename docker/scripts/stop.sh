#!/bin/bash

# Script to stop all docker containers
set -e

echo "Stopping Bank App Docker containers..."

# Determine docker-compose command
DOCKER_COMPOSE_CMD="docker-compose"
if ! command -v docker-compose &> /dev/null; then
    DOCKER_COMPOSE_CMD="docker compose"
fi

# Stop containers
$DOCKER_COMPOSE_CMD -f docker-compose.yml down

echo "All containers stopped successfully!"

