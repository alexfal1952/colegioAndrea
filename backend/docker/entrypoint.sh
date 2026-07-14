#!/bin/bash
set -e

cd /var/www/html

if [ ! -f .env ]; then
    cp .env.example .env
fi

if [ ! -d vendor ]; then
    composer install --no-interaction --prefer-dist
fi

if [ -z "$(grep '^APP_KEY=base64:' .env 2>/dev/null)" ]; then
    php artisan key:generate --force --no-interaction
fi
php artisan migrate --force --no-interaction
php artisan db:seed --force --no-interaction || true

chown -R www-data:www-data storage bootstrap/cache

exec "$@"
