FROM php:8.3-fpm

# System deps
RUN apt-get update && apt-get install -y --no-install-recommends \
    git unzip zip curl ca-certificates \
    libpq-dev libzip-dev \
    libpng-dev libjpeg62-turbo-dev libfreetype6-dev \
    && rm -rf /var/lib/apt/lists/*

# PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) pdo_pgsql zip gd opcache

# Redis extension (for queues/cache)
RUN pecl install redis && docker-php-ext-enable redis

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# PHP settings
COPY docker/app/php.ini /usr/local/etc/php/conf.d/99-krema.ini

# Non-root user (optional)
ARG UID=1000
ARG GID=1000
RUN groupadd -g ${GID} app && useradd -m -u ${UID} -g app app

# Entrypoint
COPY docker/app/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

USER app

CMD ["php-fpm"]
