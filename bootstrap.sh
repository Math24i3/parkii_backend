#!/bin/sh

cd /var/www
php artisan key:generate
echo 'APP_ID='$(uuidgen) >> .env
php artisan config:clear
php-fpm
