# HomesMenu — Laravel headless API
FROM php:8.4-cli

# System deps + PHP extensions needed by Laravel + MySQL
RUN apt-get update && apt-get install -y --no-install-recommends \
        git unzip libzip-dev default-mysql-client \
    && docker-php-ext-install pdo_mysql zip bcmath \
    && rm -rf /var/lib/apt/lists/*

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# App source + dependencies (no artisan scripts at build — entrypoint runs them
# at runtime once .env + the database are available).
COPY . .
RUN composer install --no-interaction --prefer-dist --no-scripts --optimize-autoloader \
    && chmod +x docker/entrypoint.sh \
    && chmod -R 775 storage bootstrap/cache

EXPOSE 8000
ENTRYPOINT ["sh", "docker/entrypoint.sh"]
