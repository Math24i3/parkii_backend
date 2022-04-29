#!/bin/sh

cd /var/www || return
doppler secrets -p parkii-api -c dev download --no-file --format env > .env -t dp.st.dev.XE97Q1vYTpwwJc5rGNG3IqA4SEzVPqZp6RkuUBKF9Hn
echo "APP_ID=$(uuidgen)" >> .env

php artisan config:clear
php artisan route:clear

php-fpm
