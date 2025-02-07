FROM php:8.1-fpm

# Install the necessary dependencies and tools
RUN apt-get update && apt-get install -y \
    nginx \
    supervisor \
    libpng-dev libjpeg-dev libfreetype6-dev \
    zip unzip \
    curl

# Install Composer globally
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Configuring and installing PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg && \
    docker-php-ext-install -j$(nproc) gd pdo pdo_mysql

# Removing default PHP-FPM configuration files
RUN rm -f /usr/local/etc/php-fpm.d/*.conf

# Copying PHP‑FPM, nginx and supervisor configuration files
COPY www.conf /usr/local/etc/php-fpm.d/www.conf
COPY nginx/conf.d/default.conf /etc/nginx/conf.d/default.conf
COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf

WORKDIR /var/www/html

# Copy composer files and install dependencies
COPY composer.json composer.lock ./

# Copy the remaining project files
COPY . /var/www/html

# Copying entrypoint.sh and run to install vendor
COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

# Open port 9000 (the one that Railway expects)
EXPOSE 9000

# Launch by entrypoint.sh
ENTRYPOINT ["/entrypoint.sh"]
