#!/bin/bash

# Script to start all docker containers
set -e

echo "Starting Bank App Docker containers..."

# Check if docker-compose exists
if ! command -v docker-compose &> /dev/null && ! command -v docker compose &> /dev/null; then
    echo "ERROR: docker-compose is not installed. Please install Docker Desktop or Docker Engine with Compose."
    exit 1
fi

# Determine docker-compose command
DOCKER_COMPOSE_CMD="docker-compose"
if ! command -v docker-compose &> /dev/null; then
    DOCKER_COMPOSE_CMD="docker compose"
fi

# Start containers
$DOCKER_COMPOSE_CMD -f docker-compose.yml up -d

echo "All containers started successfully!"
echo ""
echo "Service Status:"
echo "  Backend API: http://localhost:8000"
echo "  Frontend: http://localhost:5174"
echo "  MariaDB: localhost:3306"
echo "  Redis: localhost:6379"
echo ""
echo "To view logs: docker-compose logs -f"
