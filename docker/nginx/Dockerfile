FROM nginx:1.19-alpine

WORKDIR /var/www/record-shop

COPY ./docker/nginx/record-shop.conf /etc/nginx/conf.d/default.conf

COPY ./app/public /var/www/record-shop/public