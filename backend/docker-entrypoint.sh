#!/bin/sh
set -e

php artisan config:cache
php artisan route:cache
php artisan migrate --force

exec php artisan octane:frankenphp --host=0.0.0.0 --port=8000
