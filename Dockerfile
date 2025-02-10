FROM php:8.1-fpm

# updating packages and install necessary utilities
RUN apt-get update && apt-get install -y \
    nginx \
    supervisor \
    gettext-base \
    libpng-dev libjpeg-dev libfreetype6-dev \
    zip unzip \
    curl \
  && rm -rf /var/lib/apt/lists/*

# Installing Composer Globally
RUN curl -sS https://getcomposer.org/installer | php -- \
    --install-dir=/usr/local/bin \
    --filename=composer

# Setting up and installing PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd pdo pdo_mysql

# Removing default PHP-FPM configs
RUN rm -f /usr/local/etc/php-fpm.d/*.conf

# Copy our configs (Nginx, Supervisord, PHP-FPM)
COPY nginx/conf.d/default.conf.template /etc/nginx/conf.d/default.conf.template
COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY www.conf /usr/local/etc/php-fpm.d/www.conf

# copy entrypoint
COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

# Copy the application files to /var/www/html
WORKDIR /var/www/html
COPY composer.json composer.lock ./
COPY . /var/www/html

# The port we will listen to inside the container.
EXPOSE 9000

ENTRYPOINT ["/entrypoint.sh"]
