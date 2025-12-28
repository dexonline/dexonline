FROM php:7.4-apache

RUN apt-get update \
  && apt-get install -y --no-install-recommends libicu-dev unzip git gettext locales \
  && sed -i 's/^# *\(en_US\.UTF-8\)/\1/' /etc/locale.gen \
  && sed -i 's/^# *\(ro_RO\.UTF-8\)/\1/' /etc/locale.gen \
  && locale-gen \
  && update-locale LANG=ro_RO.UTF-8 LC_ALL=ro_RO.UTF-8 \
  && docker-php-ext-configure intl \
  && docker-php-ext-install -j"$(nproc)" gettext mysqli pdo_mysql intl \
  && a2enmod rewrite \
  && rm -rf /var/lib/apt/lists/*

ENV LANG=ro_RO.UTF-8
ENV LC_ALL=ro_RO.UTF-8

COPY 000-default.conf /etc/apache2/sites-available/000-default.conf

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER=1
