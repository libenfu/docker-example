FROM node as builder

ARG NODE_ENV=development
ENV NODE_ENV=${NODE_ENV}

COPY ./code/package*.json .
RUN npm i



FROM node

LABEL version="1.0"
LABEL maintainer="justinchou<zhou78620051@126.com>"

WORKDIR /data/nodejs

COPY --from=builder node_modules node_modules
COPY ./code ./

ENTRYPOINT node bin/www