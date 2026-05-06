#!/bin/bash

# Script to start backend docker containers
set -e

echo "🚀 Starting Backend Docker containers..."

# Determine docker-compose command
DOCKER_COMPOSE_CMD="docker-compose"
if ! command -v docker-compose &> /dev/null; then
    DOCKER_COMPOSE_CMD="docker compose"
fi

# Start containers
$DOCKER_COMPOSE_CMD up -d

echo "✅ Backend containers started successfully!"
echo ""
echo "📋 Service Status:"
echo "  Backend API: http://localhost:8000"
echo "  MariaDB: localhost:3306"
echo "  Redis: localhost:6379"
echo ""
echo "💡 To view logs: bash ../docker/scripts/logs.sh"

