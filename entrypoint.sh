#!/bin/bash
set -e

# Если папки vendor нет или она пуста — запускаем composer install
if [ ! -d "/var/www/html/vendor" ] || [ -z "$(ls -A /var/www/html/vendor)" ]; then
    composer install --no-dev --optimize-autoloader
fi

# Запуск supervisord (который в свою очередь запустит php-fpm и nginx)
exec /usr/bin/supervisord -n
