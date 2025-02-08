#!/bin/bash
set -e

# Генерируем конфиг nginx из шаблона
envsubst '${PORT}' < /etc/nginx/conf.d/default.conf.template > /etc/nginx/conf.d/default.conf

# Если vendor не существует или пуст, устанавливаем зависимости (production-режим)
if [ ! -d "/var/www/html/vendor" ] || [ -z "$(ls -A /var/www/html/vendor)" ]; then
    composer install --no-dev --optimize-autoloader
fi

# Запускаем supervisord
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
