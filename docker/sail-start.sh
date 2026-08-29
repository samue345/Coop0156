#!/usr/bin/env bash
set -e

cd /var/www/html

mkdir -p database
touch database/database.sqlite

php artisan migrate --force

export PHP_CLI_SERVER_WORKERS=4
php artisan queue:work --tries=3 --timeout=60 &

exec /usr/bin/php -d variables_order=EGPCS /var/www/html/artisan serve --host=0.0.0.0 --port=80
