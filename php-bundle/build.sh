#!/bin/bash

NAME=bccq/php-bundle
VERSION=$1

if [ "${VERSION}" == "" ]; then
  docker build -t ${NAME} .
else
  docker build -t ${NAME} -t ${NAME}:${VERSION} .
fi