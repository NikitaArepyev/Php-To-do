FROM php:8.2-cli

RUN docker-php-ext-install pdo pdo_mysql
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY composer.json /app/composer.json
RUN composer install --no-dev --no-interaction --prefer-dist

COPY . /app

EXPOSE 8000

CMD ["php", "-S", "0.0.0.0:8000", "router.php"]
