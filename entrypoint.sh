#!/bin/bash
set -e

# Подстановка значения переменной PORT в конфигурацию nginx
envsubst '$PORT' < /etc/nginx/conf.d/default.conf > /etc/nginx/conf.d/default.conf

# Если папка vendor пуста, устанавливаем зависимости
if [ ! -d "/var/www/html/vendor" ] || [ -z "$(ls -A /var/www/html/vendor)" ]; then
    composer install --no-dev --optimize-autoloader
fi

# Запуск supervisord, который поднимет php‑fpm и nginx
exec /usr/bin/supervisord -n
