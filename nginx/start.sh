#!/bin/sh
set -e
export PORT="${PORT:-80}"
echo "Starting on port $PORT"
envsubst '${PORT}' < /etc/nginx/templates/default.conf.template > /etc/nginx/conf.d/default.conf
nginx -t
php-fpm -D
exec nginx -g 'daemon off;'
