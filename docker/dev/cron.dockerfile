FROM php:8.1.4-fpm-alpine

RUN docker-php-ext-install pdo pdo_mysql

COPY crontab /etc/crontabs/root

# Copy existing application directory permissions
COPY --chown=www-data:www-data . /var/www

CMD ["crond", "-f"]
