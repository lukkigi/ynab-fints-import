FROM php:7.4-cli-alpine

RUN apk add composer git

COPY . .

RUN composer install

EXPOSE 8000

RUN chmod +x artisan

ENTRYPOINT [ "./artisan", "serve", "--host", "0.0.0.0" ]
