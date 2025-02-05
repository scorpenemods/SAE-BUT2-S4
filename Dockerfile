FROM php:8.1-fpm

# Обновляем список пакетов и устанавливаем необходимые зависимости
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    zip \
    unzip \
 && docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-install -j$(nproc) gd pdo pdo_mysql

WORKDIR /var/www/html

# PHP-FPM слушает на порту 9000 (внутренний для связи с Nginx)
EXPOSE 9000

CMD ["php-fpm"]
