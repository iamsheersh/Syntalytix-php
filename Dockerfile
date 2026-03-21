FROM php:8.1-fpm-alpine

# Install nginx and mysql extensions
RUN apk add --no-cache nginx mysql-client \
    && docker-php-ext-install mysqli pdo pdo_mysql

# Copy nginx config template
COPY nginx.conf /etc/nginx/nginx.conf.template

# Copy startup script
COPY start.sh /start.sh
RUN chmod +x /start.sh

# Copy project files
COPY . /var/www/html/

# Set permissions
RUN chown -R www-data:www-data /var/www/html

# Railway provides PORT env var, default to 80
EXPOSE 80

# Start using the script
CMD ["/start.sh"]
