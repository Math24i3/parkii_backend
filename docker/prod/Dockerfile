FROM php:8.1.4-fpm
# Copy composer.lock and composer.json
COPY composer.lock composer.json /var/www/

# Set working directory
WORKDIR /var/www

# Install dependencies
RUN apt-get update && apt-get install -y \
    build-essential \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    locales \
    zip \
    jpegoptim optipng pngquant gifsicle \
    vim \
    unzip \
    git \
    curl \
    libzip-dev \
    uuid-runtime \
    apt-transport-https \
    ca-certificates \
    gnupg

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# doppler install
RUN curl -sLf --retry 3 --tlsv1.2 --proto "=https" 'https://packages.doppler.com/public/cli/gpg.DE2A7741A397C129.key' | apt-key add -
RUN echo "deb https://packages.doppler.com/public/cli/deb/debian any-version main" | tee /etc/apt/sources.list.d/doppler-cli.list
RUN apt-get update && apt-get install doppler

# Install extensions
RUN pecl install redis
RUN docker-php-ext-install pdo pdo_mysql zip exif pcntl gd
RUN docker-php-ext-configure gd --with-freetype --with-jpeg
RUN docker-php-ext-enable redis

# Install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Add user for laravel application
RUN groupadd -g 1001 www
RUN useradd -u 1001 -ms /bin/bash -g www www

# Copy existing application directory contents
ADD . /var/www

ADD ./php/local.ini /usr/local/etc/php/conf.d/local.ini

RUN cat > .env

RUN composer install --optimize-autoloader --no-dev

RUN php artisan config:cache
RUN php artisan optimize
RUN php artisan route:cache
RUN php artisan cache:clear

# Copy existing application directory permissions
COPY --chown=www:www . /var/www

RUN chmod -R ug+w /var/www/storage
RUN chmod +x bootstrap.sh
RUN chown -R www:www bootstrap.sh
RUN chown -R www:www .env

# Change current user to www
USER www


# Expose port 9000 and start php-fpm server
EXPOSE 9000
CMD ["./bootstrap.sh"]
