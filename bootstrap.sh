#!/bin/sh

cd /var/www || return
doppler secrets -p parkii-api -c prd download --no-file --format env > .env -t dp.st.prd.3RrA2C92MKqIFSuMFh5x8uRyTbC4fya3rop0J8WBGtc
echo "APP_ID=$(uuidgen)" >> .env

php artisan migrate --force
php artisan config:clear
php artisan route:clear

php-fpm
