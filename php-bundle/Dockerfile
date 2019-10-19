FROM phpswoole/swoole:4.4.5-php7.3

LABEL version="1.0"
LABEL maintainer="justinchou<zhou78620051@126.com>"

# ADD into image, COPY into container

ADD ./deps /data

WORKDIR /data

# RUN into image when `docker build`, CMD into container when `docker run`

# Install cmake
RUN apt-get install -y cmake

# Install libzip
RUN tar -zxf libzip-1.5.2.tar.gz
RUN cd libzip-1.5.2 \
        && mkdir build \
        && cd build && cmake .. \
        && make -j4 \
        && make install

# Install gd depenencies
RUN apt-get update && apt-get install -y \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libmcrypt-dev \
        libxml2-dev

# Install php extensition
RUN docker-php-ext-install \
        zip bcmath pdo_mysql mysqli mbstring opcache soap \
        && docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ \
        && docker-php-ext-install gd
