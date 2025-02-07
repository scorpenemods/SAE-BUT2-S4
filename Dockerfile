# image PHP-FPM
FROM php:8.1-fpm

# Install: nginx, supervisor and PHP dependencies
RUN apt-get update && apt-get install -y \
    nginx \
    supervisor \
    libpng-dev libjpeg-dev libfreetype6-dev \
    zip unzip

# Setting PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg && \
    docker-php-ext-install -j$(nproc) gd pdo pdo_mysql

# Deleting default PHP‑FPM config
RUN rm -f /usr/local/etc/php-fpm.d/*.conf

# Copy our custom PHP-FPM configuration (note: listening on 127.0.0.1:9001)
COPY www.conf /usr/local/etc/php-fpm.d/www.conf

# Copy nginx config
COPY nginx.conf /etc/nginx/nginx.conf
COPY default.conf /etc/nginx/conf.d/default.conf

# supervisor config
COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Copy the application files to the working directory
WORKDIR /var/www/html
COPY . /var/www/html

# Open port 9000 (the one that Railway expects)
EXPOSE 9000

# Start supervisord, which will start both nginx and PHP‑FPM
CMD ["/usr/bin/supervisord", "-n"]
