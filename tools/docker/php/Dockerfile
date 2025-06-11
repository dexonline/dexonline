FROM php:7.4-apache
RUN apt-get update && apt-get install -y libicu-dev unzip git
RUN docker-php-ext-configure intl
RUN docker-php-ext-install -j$(nproc) gettext mysqli pdo_mysql intl && a2enmod rewrite
COPY 000-default.conf /etc/apache2/sites-available/000-default.conf

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER=1
