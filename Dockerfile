FROM php:8.1-fpm-alpine

# Install nginx and mysql extensions
RUN apk add --no-cache nginx mysql-client \
    && docker-php-ext-install mysqli pdo pdo_mysql

# Copy nginx config
COPY nginx.conf /etc/nginx/nginx.conf

# Copy project files
COPY . /var/www/html/

# Set permissions
RUN chown -R www-data:www-data /var/www/html

# Expose port
EXPOSE 80

# Start nginx and php-fpm
CMD sh -c "php-fpm -D && nginx -g 'daemon off;'"
