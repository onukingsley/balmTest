#!/usr/bin/env bash

echo "=== Laravel Deploy Script Starting ==="

# Generate APP_KEY if not present
if [ -z "$APP_KEY" ]; then
  echo "Generating APP_KEY..."
  php artisan key:generate --force
fi

echo "Running migrations..."
php artisan migrate --force

echo "Caching config, routes and views..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "Linking storage..."
php artisan storage:link

echo "=== Laravel Deploy Script Completed ==="
