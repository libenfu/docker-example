FROM centos:7

LABEL version="1.1"
LABEL maintainer="justinchou<zhou78620051@126.com>"

# ADD into image, COPY into container

ADD ./deps /data

WORKDIR /data

# RUN into image when `docker build`, CMD into container when `docker run`

RUN yum install -y \
  gcc gcc-c++ autoconf libjpeg libjpeg-devel \
  enchant-devel pam-devel libc-client \
  libc-client-devel libpng libpng-devel \
  freetype freetype-devel libpng libpng-devel \
  libxml2 libxml2-devel zlib zlib-devel glibc \
  glibc-devel glib2 glib2-devel bzip2 bzip2-devel \
  ncurses curl openssl-devel gdbm-devel db4-devel \
  libXpm-devel libX11-devel gd-devel gmp-devel \
  readline-devel libxslt-devel expat-devel \
  xmlrpc-c xmlrpc-c-devel make cmake

RUN yum install -y epel-release

RUN yum install -y redis mysql

# Install libmcrypt manually
RUN tar zxf libmcrypt-2.5.7.tar.gz
RUN cd libmcrypt-2.5.7 && ./configure &&  make && make install


# node 
RUN tar zxf node-v13.2.0-linux-x64.tar.gz
RUN cd node-v13.2.0-linux-x64

RUN ln -s /data/node-v13.2.0-linux-x64/bin/node /usr/bin/node
RUN ln -s /data/node-v13.2.0-linux-x64/bin/npm /usr/bin/npm

