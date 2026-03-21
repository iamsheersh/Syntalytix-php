FROM php:8.1-cli

# Install mysql extensions
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Copy project files
COPY . /var/www/html/

# Set permissions
RUN chown -R www-data:www-data /var/www/html

WORKDIR /var/www/html

# Use PHP built-in server (simpler, works reliably)
CMD php -S 0.0.0.0:$PORT -t .
