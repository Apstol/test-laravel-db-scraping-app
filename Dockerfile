FROM php:8.5.7-cli-alpine

RUN docker-php-ext-install pdo_mysql

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
 
WORKDIR /app

COPY ./app .

CMD /bin/sh -c "composer install && php artisan key:generate && tail -f"
