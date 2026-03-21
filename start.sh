#!/bin/sh

# Replace $PORT in nginx config with actual port from environment
export PORT=${PORT:-80}
envsubst '$PORT' < /etc/nginx/nginx.conf.template > /etc/nginx/nginx.conf

# Start PHP-FPM
php-fpm -D

# Start Nginx
nginx -g 'daemon off;'
