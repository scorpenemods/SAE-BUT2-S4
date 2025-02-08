FROM php:8.1-fpm

# Обновляем пакеты и устанавливаем необходимые утилиты
RUN apt-get update && apt-get install -y \
    nginx \
    supervisor \
    gettext-base \
    libpng-dev libjpeg-dev libfreetype6-dev \
    zip unzip \
    curl \
  && rm -rf /var/lib/apt/lists/*

# Установка Composer глобально
RUN curl -sS https://getcomposer.org/installer | php -- \
    --install-dir=/usr/local/bin \
    --filename=composer

# Настройка и установка PHP‑расширений
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd pdo pdo_mysql

# Удаляем дефолтные конфиги PHP-FPM
RUN rm -f /usr/local/etc/php-fpm.d/*.conf

# Копируем наши конфиги (Nginx, Supervisord, PHP-FPM)
COPY nginx/conf.d/default.conf.template /etc/nginx/conf.d/default.conf.template
COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY www.conf /usr/local/etc/php-fpm.d/www.conf

# Копируем entrypoint
COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

# Копируем файлы приложения в /var/www/html
WORKDIR /var/www/html
COPY composer.json composer.lock ./
# Установка зависимостей на этапе билдера (если нужно/полезно)
# но обычно полезнее ставить в entrypoint, чтобы не залип кэш
# RUN composer install --no-dev --optimize-autoloader

COPY . /var/www/html

# Порт, который будем слушать внутри контейнера.
# Railway сам пробросит этот порт во внешний мир,
# при этом задав переменную среды PORT=<какой-то_динамический>.
EXPOSE 9000

ENTRYPOINT ["/entrypoint.sh"]
