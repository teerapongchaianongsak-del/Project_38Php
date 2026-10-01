#!/bin/sh
set -e
export PORT="${PORT:-80}"
envsubst '${PORT}' < /etc/nginx/templates/default.conf.template > /etc/nginx/conf.d/default.conf
php-fpm -D
exec nginx -g 'daemon off;'
