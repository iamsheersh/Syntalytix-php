FROM php:8.1-cli

# Install mysql extensions
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Copy startup script
COPY start.sh /start.sh
RUN chmod +x /start.sh

# Copy project files
COPY . /var/www/html/

# Set permissions
RUN chown -R www-data:www-data /var/www/html

WORKDIR /var/www/html

# Use start script
CMD ["/start.sh"]
