FROM bccq/php-build

LABEL version="1.0"
LABEL maintainer="justinchou<zhou78620051@126.com>"

WORKDIR /var/www

COPY --chown=www:www ./config/php-fpm.d /usr/local/php7/etc/php-fpm.d
COPY --chown=www:www ./config/php-fpm.conf /usr/local/php7/etc/
COPY --chown=www:www ./config/php.ini /usr/local/php7/etc/

# 代码复制到容器内
COPY ./MyAdmin /var/www

ENTRYPOINT [ "php-fpm" ]

# composer 加载
COPY ./deps/composer.phar /usr/local/bin/composer
RUN chmod +x /usr/local/bin/composer

RUN rm /usr/bin/node /usr/bin/npm
RUN ln -s /data/node-v13.2.0-linux-x64/bin/node /usr/bin/node
RUN ln -s /data/node-v13.2.0-linux-x64/bin/npm /usr/bin/npm

RUN npm install -g bower --registry=https://registry.npm.taobao.org

