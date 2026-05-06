#!/bin/bash

# Script to open shell in docker container
set -e

# Determine docker-compose command
DOCKER_COMPOSE_CMD="docker-compose"
if ! command -v docker-compose &> /dev/null; then
    DOCKER_COMPOSE_CMD="docker compose"
fi

# Check if container name was provided
if [ -z "$1" ]; then
    echo "❌ Usage: bash docker/scripts/shell.sh <container>"
    echo ""
    echo "📋 Available containers:"
    $DOCKER_COMPOSE_CMD -f docker-compose.yml ps --services
    exit 1
fi

CONTAINER=$1

echo "🔓 Opening shell in $CONTAINER container..."
echo ""

$DOCKER_COMPOSE_CMD -f docker-compose.yml exec $CONTAINER /bin/sh

