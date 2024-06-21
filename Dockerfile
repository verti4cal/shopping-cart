FROM php:8.3-apache AS php_base

COPY vhost.conf /etc/apache2/sites-available/000-default.conf

ENV TZ=Europe/Berlin

RUN a2enmod negotiation
RUN a2enmod rewrite
RUN a2enmod headers

USER www-data

# Copy files
COPY --chown=www-data ./ /var/www/html/

USER root

# install dependencies
RUN apt-get update
RUN apt-get install libzip-dev -y

# install php extensions
RUN docker-php-source extract \
    && docker-php-ext-install pdo_mysql \
    && docker-php-ext-install zip \
    && docker-php-source delete

# install composer
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

RUN rm .env*

FROM php_base AS php_dev

ENV APP_ENV=dev

RUN mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"

# change php.ini settings
RUN echo "date.timezone = Europe/Berlin" >> "$PHP_INI_DIR/php.ini"
RUN echo "log_errors = On" >> "$PHP_INI_DIR/php.ini"
RUN echo "error_log = /var/www/html/log/php_errors.log" >> "$PHP_INI_DIR/php.ini"
RUN sed -i "s/session.gc_maxlifetime = 1440/session.gc_maxlifetime = 86400/g" "$PHP_INI_DIR/php.ini"
RUN sed -i "s/memory_limit = 128M/memory_limit = 512M/g" "$PHP_INI_DIR/php.ini"
RUN sed -i "s/upload_max_filesize = 2M/upload_max_filesize = 100M/g" "$PHP_INI_DIR/php.ini"
RUN sed -i "s/post_max_size = 8M/post_max_size = 100M/g" "$PHP_INI_DIR/php.ini"
RUN sed -i "s/max_execution_time = 30/max_execution_time = 300/g" "$PHP_INI_DIR/php.ini"
RUN sed -i "s/max_input_time = 60/max_input_time = 300/g" "$PHP_INI_DIR/php.ini"

COPY --chown=www-data .env.dev.local .env
COPY --chown=www-data .env.test .env.test

# install xdebug extensions
RUN pecl install xdebug \
    && docker-php-ext-enable xdebug

USER www-data

RUN composer install --optimize-autoloader

RUN ln -s /var/www/html/tests/coverage /var/www/html/public/coverage

FROM php_base AS php_prod

ENV APP_ENV=prod

RUN docker-php-ext-install opcache \
    && docker-php-source delete

# change apache settings
RUN echo "ServerTokens Prod" >> /etc/apache2/apache2.conf
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# change php.ini settings
RUN echo "date.timezone = Europe/Berlin" >> "$PHP_INI_DIR/php.ini"
RUN echo "log_errors = On" >> "$PHP_INI_DIR/php.ini"
RUN echo "error_log = /var/www/html/log/php_errors.log" >> "$PHP_INI_DIR/php.ini"
RUN sed -i "s/session.gc_maxlifetime = 1440/session.gc_maxlifetime = 86400/g" "$PHP_INI_DIR/php.ini"
RUN sed -i "s/memory_limit = 128M/memory_limit = 512M/g" "$PHP_INI_DIR/php.ini"
RUN sed -i "s/upload_max_filesize = 2M/upload_max_filesize = 100M/g" "$PHP_INI_DIR/php.ini"
RUN sed -i "s/post_max_size = 8M/post_max_size = 100M/g" "$PHP_INI_DIR/php.ini"
RUN sed -i "s/max_execution_time = 30/max_execution_time = 300/g" "$PHP_INI_DIR/php.ini"
RUN sed -i "s/max_input_time = 60/max_input_time = 300/g" "$PHP_INI_DIR/php.ini"
RUN echo "opcache.enable=1" >> "$PHP_INI_DIR/php.ini"
RUN echo "opcache.enable_cli=1" >> "$PHP_INI_DIR/php.ini"
RUN echo "opcache.interned_strings_buffer=4" >> "$PHP_INI_DIR/php.ini"
RUN echo "opcache.max_accelerated_files=2000" >> "$PHP_INI_DIR/php.ini"
RUN echo "opcache.memory_consumption=64" >> "$PHP_INI_DIR/php.ini"
RUN echo "opcache.save_comments=1" >> "$PHP_INI_DIR/php.ini"
RUN echo "opcache.revalidate_freq=2" >> "$PHP_INI_DIR/php.ini"
RUN echo "opcache.validate_timestamps=0" >> "$PHP_INI_DIR/php.ini"

COPY --chown=www-data .env.prod.local .env

USER www-data

RUN composer install --optimize-autoloader --no-dev