#!/bin/sh
set -e

cd /var/www/backend

if [ ! -f vendor/autoload.php ]; then
  composer install \
    --prefer-dist \
    --no-interaction \
    --no-progress \
    --optimize-autoloader
fi

mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/app/public bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache || true
chmod -R ug+rwx storage bootstrap/cache || true

exec "$@"