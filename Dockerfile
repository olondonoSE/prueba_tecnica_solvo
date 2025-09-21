FROM php:8.2-cli

# Tools + extensions
RUN apt-get update && apt-get install -y \
    git unzip libicu-dev libzip-dev zip curl gnupg \
    && docker-php-ext-install pdo_mysql intl zip

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Create app dir
WORKDIR /var/www/html

# Copy composer files and install dependencies at build time (caching)
COPY composer.json composer.lock* ./
RUN composer install --no-interaction --prefer-dist --no-scripts

# Copy rest
COPY . /var/www/html

# Instalar Symfony CLI
RUN curl -sS https://get.symfony.com/cli/installer | bash \
    && mv /root/.symfony*/bin/symfony /usr/local/bin/symfony

# Expose port (when using built-in server)
EXPOSE 8000
