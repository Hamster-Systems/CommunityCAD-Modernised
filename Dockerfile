FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    git unzip libicu-dev libzip-dev \
    libpng-dev libjpeg62-turbo-dev libfreetype6-dev \
    && docker-php-ext-configure gd \
       --with-freetype --with-jpeg \
    && docker-php-ext-install -j2 \
       pdo_mysql bcmath intl zip gd \
    && a2enmod rewrite headers \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN sed -ri \
    's!/var/www/html!/var/www/html/public!g' \
    /etc/apache2/sites-available/000-default.conf

RUN printf '<Directory /var/www/html/public>\nAllowOverride All\nRequire all granted\n</Directory>\n' \
    > /etc/apache2/conf-available/communitycad.conf \
    && a2enconf communitycad

WORKDIR /var/www/html

COPY . .

RUN composer install \
    --no-dev \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader \
    --no-scripts \
    && mkdir -p storage/framework/cache \
       storage/framework/sessions \
       storage/framework/views \
       bootstrap/cache \
    && chown -R www-data:www-data \
       storage bootstrap/cache

EXPOSE 80
