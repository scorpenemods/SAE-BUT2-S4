#!/bin/bash
set -e

# Если директория vendor отсутствует или пуста, запускаем composer install
if [ ! -d "/var/www/html/vendor" ] || [ -z "$(ls -A /var/www/html/vendor)" ]; then
    composer install --no-dev --optimize-autoloader
fi

# Запуск supervisor (и, соответственно, nginx и php-fpm)
exec /usr/bin/supervisord -n
