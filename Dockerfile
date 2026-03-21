FROM php:8.1-apache

# Fix MPM conflict - disable event MPM and enable prefork MPM
RUN a2dismod mpm_event && a2enmod mpm_prefork

# Enable mod_rewrite
RUN a2enmod rewrite

# Install MySQL extensions
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Set document root
ENV APACHE_DOCUMENT_ROOT /var/www/html

# Copy project files
COPY . /var/www/html/

# Set permissions
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80
