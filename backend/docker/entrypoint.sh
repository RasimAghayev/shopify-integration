#!/bin/bash
set -e

# Create .env if it doesn't exist
if [ ! -f ".env" ] && [ -f ".env.example" ]; then
    echo "Creating .env from .env.example..."
    cp .env.example .env
fi

# Create necessary directories
mkdir -p storage/logs storage/framework/cache storage/framework/sessions storage/framework/views
mkdir -p bootstrap/cache
mkdir -p vendor
chmod -R 777 storage bootstrap/cache vendor 2>/dev/null || true

# Install dependencies if vendor folder is empty or autoload.php doesn't exist
if [ ! -f "vendor/autoload.php" ]; then
    echo "Installing Composer dependencies..."
    COMPOSER_ALLOW_SUPERUSER=1 composer install --no-interaction
fi

# Generate app key if not set
if [ -f ".env" ] && [ -f "vendor/autoload.php" ]; then
    if ! grep -q "^APP_KEY=base64:" .env; then
        echo "Generating application key..."
        php artisan key:generate --no-interaction 2>/dev/null || true
    fi
fi

exec "$@"
