#!/bin/sh

cd /var/www
doppler secrets -p parkii-api -c prd download --no-file --format env > .env -t dp.st.dev.XE97Q1vYTpwwJc5rGNG3IqA4SEzVPqZp6RkuUBKF9Hn
echo 'APP_ID='$(uuidgen) >> .env
php artisan config:clear
php-fpm
