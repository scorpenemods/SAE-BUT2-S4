#!/bin/bash
set -e

# Generate nginx config from template
envsubst '${PORT}' < /etc/nginx/conf.d/default.conf.template > /etc/nginx/conf.d/default.conf

# If vendor does not exist or is empty, install dependencies
if [ ! -d "/var/www/html/vendor" ] || [ -z "$(ls -A /var/www/html/vendor)" ]; then
    composer install --no-dev --optimize-autoloader
fi

# Launch supervisord
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
