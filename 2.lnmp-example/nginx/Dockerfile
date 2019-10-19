FROM nginx

LABEL version="1.0"
LABEL maintainer="justinchou<zhou78620051@126.com>"

WORKDIR /data

COPY --chown=nginx:nginx ./config/conf.d /etc/nginx/conf.d/
COPY --chown=nginx:nginx ./config/fastcgi.conf /etc/nginx/
COPY --chown=nginx:nginx ./config/nginx.conf /etc/nginx/
