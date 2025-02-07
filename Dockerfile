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

# Настройка и установка PHP‑расширений
RUN docker-php-ext-configure gd --with-freetype --with-jpeg && \
    docker-php-ext-install -j$(nproc) gd pdo pdo_mysql

# Удаляем стандартные конфигурационные файлы PHP‑FPM
RUN rm -f /usr/local/etc/php-fpm.d/*.conf

# Копирование конфигурационных файлов:
# - www.conf для php‑fpm
# - default.conf для nginx
# - supervisord.conf для supervisor
COPY www.conf /usr/local/etc/php-fpm.d/www.conf
COPY nginx/conf.d/default.conf.template /etc/nginx/conf.d/default.conf.template
COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf

WORKDIR /var/www/html

# Копирование файлов composer и установка зависимостей
COPY composer.json composer.lock ./
COPY . /var/www/html

# Копирование entrypoint.sh и выдача прав на выполнение
COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

# Приложение будет слушать на порту 9000
EXPOSE 9000

ENTRYPOINT ["/entrypoint.sh"]
