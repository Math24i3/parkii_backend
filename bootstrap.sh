#!/bin/sh

cd /var/www
echo 'APP_ID='$(uuidgen) >> .env
php artisan config:clear
php-fpm
