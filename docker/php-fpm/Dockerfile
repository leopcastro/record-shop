FROM php:7.4-fpm-alpine AS PROD

WORKDIR /var/www/record-shop

COPY ./app /var/www/record-shop

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

FROM prod AS DEV

COPY ./docker/php-fpm/dev.xdebug.ini /usr/local/etc/php/conf.d/dev.xdebug.ini

RUN apk --update --no-cache add autoconf g++ make && \
    pecl install -f xdebug && \
    docker-php-ext-enable xdebug && \
    apk del --purge autoconf g++ make