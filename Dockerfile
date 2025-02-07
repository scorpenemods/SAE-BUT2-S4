FROM php:8.1-fpm

# Установка необходимых пакетов
RUN apt-get update && apt-get install -y \
    nginx \
    supervisor \
    libpng-dev libjpeg-dev libfreetype6-dev \
    zip unzip \
    curl

# Установка Composer глобально
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Настройка и установка PHP‑расширений
RUN docker-php-ext-configure gd --with-freetype --with-jpeg && \
    docker-php-ext-install -j$(nproc) gd pdo pdo_mysql

# Удаляем дефолтные конфигурационные файлы PHP‑FPM
RUN rm -f /usr/local/etc/php-fpm.d/*.conf

# Копирование файлов конфигурации
COPY www.conf /usr/local/etc/php-fpm.d/www.conf
COPY nginx/conf.d/default.conf /etc/nginx/conf.d/default.conf
COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf

WORKDIR /var/www/html

# Копирование файлов проекта и composer файлов
COPY composer.json composer.lock ./
COPY . /var/www/html

# Копирование скрипта запуска
COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

# Приложение будет слушать на порту, заданном переменной PORT (9000)
EXPOSE 9000

ENTRYPOINT ["/entrypoint.sh"]
