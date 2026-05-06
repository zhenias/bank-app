#!/bin/bash

# Script to start frontend docker container
set -e

echo "🚀 Starting Frontend Docker container..."

# Determine docker-compose command
DOCKER_COMPOSE_CMD="docker-compose"
if ! command -v docker-compose &> /dev/null; then
    DOCKER_COMPOSE_CMD="docker compose"
fi

# Start container
$DOCKER_COMPOSE_CMD up -d

echo "✅ Frontend container started successfully!"
echo ""
echo "📋 Service Status:"
echo "  Frontend: http://localhost:5174"
echo ""
echo "📝 To view logs: bash docker/scripts/logs.sh"

