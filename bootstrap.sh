#!/bin/sh

cd /var/www
doppler secrets -p parkii-api -c prd download --no-file --format env > .env -t dp.st.prd.O5yoHNo8fSLbF3YKywo81JuHtEMKH1AWmP03loY6L2L
echo 'APP_ID='$(uuidgen) >> .env

php artisan config:clear

php-fpm
