#!/bin/bash
set -e

# Install and set dependencies
if [ ! -d "/var/www/html/vendor" ] || [ -z "$(ls -A /var/www/html/vendor)" ]; then
    composer install --no-dev --optimize-autoloader
fi

# launch supervisord
exec /usr/bin/supervisord -n
