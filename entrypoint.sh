#!/bin/bash
set -e

# Подставляем значение переменной PORT в шаблон nginx-конфигурации
envsubst '$PORT' < /etc/nginx/conf.d/default.conf.template > /etc/nginx/conf.d/default.conf

# Если директория vendor отсутствует или пуста, запускаем composer install
if [ ! -d "/var/www/html/vendor" ] || [ -z "$(ls -A /var/www/html/vendor)" ]; then
    composer install --no-dev --optimize-autoloader
fi

# Запуск supervisor (который стартует php-fpm и nginx)
exec /usr/bin/supervisord -n
