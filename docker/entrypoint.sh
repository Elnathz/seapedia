#!/bin/sh
set -e

# Cache config/routes/views for production
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Ensure storage directory structure exists
mkdir -p /app/storage/app/public \
         /app/storage/logs \
         /app/storage/framework/cache/data \
         /app/storage/framework/sessions \
         /app/storage/framework/views
chown -R www-data:www-data /app/storage /app/bootstrap/cache

exec "$@"
