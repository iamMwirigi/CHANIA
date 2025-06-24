FROM php:8.1-fpm-alpine

WORKDIR /var/www/html

# Install system dependencies
RUN apk add --no-cache curl git

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_mysql

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy composer.json and composer.lock
COPY composer*.json ./

# Install dependencies
RUN composer install --no-dev --no-interaction --no-plugins --no-scripts --prefer-dist

# Copy existing application directory contents
COPY . /var/www/html

# Copy user permissions
COPY --chown=www-data:www-data . /var/www/html

# Expose port 9000 and start php-fpm server
EXPOSE 9000
CMD ["php-fpm"] 