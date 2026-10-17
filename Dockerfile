FROM php:8.3-apache
RUN docker-php-ext-install mysqli
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
COPY . /var/www/html/
EXPOSE 80