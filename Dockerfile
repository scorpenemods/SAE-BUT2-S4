# Dockerfile-php
FROM php:8.1-fpm

# Update the package list and install dependencies
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    zip \
    unzip \
 && docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-install -j$(nproc) gd pdo pdo_mysql

# set work directory
WORKDIR /var/www/html

# (test) install Composer:
# COPY composer.lock composer.json /var/www/html/
# RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
# RUN composer install

# Port 9000 for PHP-FPM (default)
EXPOSE 9000

# Start PHP-FPM
CMD ["php-fpm"]
