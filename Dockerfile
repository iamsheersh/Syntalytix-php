FROM php:8.1-fpm-alpine

# Install nginx, mysql extensions, and gettext for envsubst
RUN apk add --no-cache nginx mysql-client gettext \
    && docker-php-ext-install mysqli pdo pdo_mysql

# Copy startup script
COPY start.sh /start.sh
RUN chmod +x /start.sh

# Copy project files
COPY . /var/www/html/

# Set permissions
RUN chown -R www-data:www-data /var/www/html

# Expose port
EXPOSE 80

# Start using the script
CMD ["/start.sh"]
