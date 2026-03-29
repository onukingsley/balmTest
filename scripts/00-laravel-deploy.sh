#!/usr/bin/env bash

echo "=== Laravel Deploy Script Starting ==="

# Generate key if missing
if [ -z "$APP_KEY" ]; then
  php artisan key:generate --force
fi

echo "Running migrations..."
php artisan migrate --force

echo "Caching Laravel..."
php artisan config:clear
php artisan route:clear
php artisan view:clear

php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "Storage link..."
php artisan storage:link

echo "=== Laravel Deploy Script Finished ==="
