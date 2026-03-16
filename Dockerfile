FROM php:8.4-fpm

RUN apt-get update && apt-get install -y \
    git \
    curl \
    unzip \
    libpq-dev \
    libzip-dev \
    libonig-dev \
    libicu-dev \
    zip \
    && docker-php-ext-install pdo pdo_pgsql pgsql mbstring zip intl

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
WORKDIR /var/www/html

COPY . /var/www/html
RUN git config --global --add safe.directory /var/www/html
RUN composer install --no-interaction --prefer-dist --optimize-autoloader
RUN cp .env.example .env || true
RUN php artisan key:generate
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 9000
CMD ["php-fpm"]
