FROM php:7.4-fpm-alpine AS PROD

COPY ./app /var/www/record-shop

WORKDIR /var/www/record-shop

FROM prod AS DEV

COPY ./docker/php-fpm/dev.xdebug.ini /usr/local/etc/php/conf.d/dev.xdebug.ini

RUN apk --update --no-cache add autoconf g++ make && \
    pecl install -f xdebug && \
    docker-php-ext-enable xdebug && \
    apk del --purge autoconf g++ make