#!/bin/bash

# Script to stop backend docker containers
set -e

echo "🛑 Stopping Backend Docker containers..."

# Determine docker-compose command
DOCKER_COMPOSE_CMD="docker-compose"
if ! command -v docker-compose &> /dev/null; then
    DOCKER_COMPOSE_CMD="docker compose"
fi

# Stop containers
$DOCKER_COMPOSE_CMD down

echo "✅ Backend containers stopped successfully!"

