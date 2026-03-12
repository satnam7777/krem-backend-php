FROM php:8.3-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y --no-install-recommends \
    nginx supervisor git unzip zip curl ca-certificates \
    libpq-dev libzip-dev libpng-dev libjpeg62-turbo-dev libfreetype6-dev \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) pdo_pgsql zip gd opcache

# Install Redis
RUN pecl install redis && docker-php-ext-enable redis

# Copy Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy application code
COPY . .

# Ensure storage and bootstrap/cache exist and are writable
RUN mkdir -p storage/framework/sessions \
    && mkdir -p storage/framework/views \
    && mkdir -p storage/framework/cache \
    && mkdir -p storage/logs \
    && mkdir -p bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Install dependencies (production optimized)
RUN composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev

# Set final permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Copy Nginx and Supervisor configs
COPY docker/nginx/default.conf /etc/nginx/sites-available/default
COPY docker/render/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Expose port (Render uses $PORT, usually 80 or 10000)
EXPOSE 80

# Start Supervisor to run both PHP-FPM and Nginx
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
