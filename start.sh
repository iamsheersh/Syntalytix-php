#!/bin/sh

# Get PORT from environment, default to 80
PORT=${PORT:-80}

# Generate nginx config with actual port
sed "s/\$PORT/$PORT/g" /var/www/html/nginx.conf > /etc/nginx/nginx.conf

# Start PHP-FPM
php-fpm -D

# Start Nginx
nginx -g 'daemon off;'
