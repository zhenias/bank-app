#!/bin/sh
set -e

echo "🚀 Starting Laravel application..."

if [ -f "/app/storage/oauth-private.key" ]; then
    chmod 600 /app/storage/oauth-private.key
    echo "Fixed permissions for oauth-private.key"
fi

if [ -f "/app/storage/oauth-public.key" ]; then
    chmod 600 /app/storage/oauth-public.key
    echo "Fixed permissions for oauth-public.key"
fi

# Copy conf file to container
echo "📁 Copying configuration files..."
cp -f docker/nginx.conf /etc/nginx/http.d/default.conf
cp -f docker/php-fpm.conf /usr/local/etc/php-fpm.d/www.conf
cp -f docker/supervisord.conf /etc/supervisord.conf

# PHP-FPM && Logs
mkdir -p /var/run/php-fpm /var/log/php-fpm /var/log/nginx
chown -R nobody:nobody /var/run/php-fpm /var/log/php-fpm
chown -R nginx:nginx /var/log/nginx

# Conf file for laravel.
mkdir -p storage/logs bootstrap/cache && chmod -R 775 storage bootstrap/cache

# Check if composer.json exists
if [ ! -f "composer.json" ]; then
    echo "❌ ERROR: No composer.json found!"
    exit 1
fi

# Check if vendor/autoload.php exists
if [ ! -f "vendor/autoload.php" ]; then
    echo "📦 Installing Composer dependencies..."
    composer install --no-interaction --optimize-autoloader
    echo "✅ Composer dependencies installed"
fi

# Generate app key if not exists
if [ ! -f .env ]; then
    echo "📄 Creating .env from .env.example..."
    cp .env.example .env
fi

if [ -z "$APP_KEY" ] || [ "$APP_KEY" = "" ]; then
    echo "🔑 Generating APP_KEY..."
    php artisan key:generate --force
fi

# Run migrations
echo "🔄 Running database migrations..."
php artisan migrate --force

# Cache configuration
echo "🔧 Caching configuration..."
php artisan optimize:clear

echo ""
echo "✅ Laravel application is ready!"
echo ""

# Start supervisord
exec /usr/bin/supervisord -c /etc/supervisord.conf
