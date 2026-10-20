FROM dunglas/frankenphp:1.4-php8.4

RUN install-php-extensions \
    pdo_pgsql \
    pcntl \
    zip \
    opcache

RUN apt-get update && apt-get install -y --no-install-recommends \
    git unzip \
    && rm -rf /var/lib/apt/lists/*

# Install composer
COPY --from=composer:2.8 /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Layer cache: install dependencies first
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --prefer-dist --no-interaction

# Copy application code
COPY . .

RUN composer dump-autoload --optimize --no-dev --no-scripts

# Create storage directories and set permissions
RUN mkdir -p storage/framework/{sessions,views,cache} \
    && mkdir -p storage/logs \
    && mkdir -p bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Copy entrypoint script
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 8000

ENTRYPOINT ["docker-entrypoint.sh"]
