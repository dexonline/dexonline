FROM php:7.4-apache

# System packages
RUN apt-get update \
  && apt-get install -y --no-install-recommends \
       curl ca-certificates gnupg \
  && mkdir -p /etc/apt/keyrings \
  && curl -fsSL https://deb.nodesource.com/gpgkey/nodesource-repo.gpg.key \
       | gpg --dearmor -o /etc/apt/keyrings/nodesource.gpg \
  && echo "deb [signed-by=/etc/apt/keyrings/nodesource.gpg] https://deb.nodesource.com/node_18.x nodistro main" \
       > /etc/apt/sources.list.d/nodesource.list \
  && apt-get update \
  && apt-get install -y --no-install-recommends \
       libicu-dev \
       unzip \
       git \
       gettext \
       locales \
       gcc \
       default-mysql-client \
       nodejs \
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

# Apache vhost — serves /dexonline/www/ to match the test suite base URL
COPY apache.conf /etc/apache2/sites-available/000-default.conf

# selenium-side-runner — baked in so local runs don't need a network install
RUN npm install -g selenium-side-runner@3

# Composer (for any future dependency installs)
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER=1
