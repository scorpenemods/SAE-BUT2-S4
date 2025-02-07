FROM php:8.1-fpm

# Установка необходимых пакетов
RUN apt-get update && apt-get install -y \
    nginx \
    supervisor \
    libpng-dev libjpeg-dev libfreetype6-dev \
    zip unzip \
    curl

# Установка Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Установка PHP‑расширений
RUN docker-php-ext-configure gd --with-freetype --with-jpeg && \
    docker-php-ext-install -j$(nproc) gd pdo pdo_mysql

# Удаляем стандартные конфигурационные файлы PHP‑FPM
RUN rm -f /usr/local/etc/php-fpm.d/*.conf

# Копируем конфигурации для php‑fpm, nginx и supervisor
COPY www.conf /usr/local/etc/php-fpm.d/www.conf
COPY nginx/conf.d/default.conf /etc/nginx/conf.d/default.conf
COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf

WORKDIR /var/www/html

# Копируем composer файлы и устанавливаем зависимости
COPY composer.json composer.lock ./
COPY . /var/www/html

# Копируем entrypoint и даём права на выполнение
COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

# Открываем порт, который будет слушать приложение
EXPOSE 9000

ENTRYPOINT ["/entrypoint.sh"]
