FROM bccq/php-build

LABEL version="1.0"
LABEL maintainer="justinchou<zhou78620051@126.com>"

WORKDIR /var/www

COPY --chown=www:www ./config/php-fpm.d /usr/local/php7/etc/php-fpm.d
COPY --chown=www:www ./config/php-fpm.conf /usr/local/php7/etc/
COPY --chown=www:www ./config/php.ini /usr/local/php7/etc/

COPY ./code /var/www

ENTRYPOINT [ "php-fpm" ]
