FROM composer:2 AS composer

FROM php:8.4-cli-bookworm
COPY --from=composer /usr/bin/composer /usr/local/bin/composer

RUN apt-get update \
    && apt-get install -y --no-install-recommends git unzip \
    && docker-php-ext-install pdo_mysql \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /app
COPY composer.json composer.lock ./
RUN COMPOSER_ALLOW_SUPERUSER=1 composer install --no-interaction --prefer-dist --no-scripts --no-autoloader

COPY . .
RUN COMPOSER_ALLOW_SUPERUSER=1 composer dump-autoload --optimize --no-interaction \
    && install -d -o www-data -g www-data /var/lib/handmade \
    && touch /var/lib/handmade/.volume \
    && chmod -R a+rX /app \
    && ln -s /var/lib/handmade/.env /app/.env \
    && chown -R www-data:www-data /app/bootstrap/cache

COPY docker/php.ini /usr/local/etc/php/conf.d/handmade.ini
USER www-data
EXPOSE 8000

ENTRYPOINT ["sh", "/app/docker/entrypoint.sh"]
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000", "--no-reload"]
