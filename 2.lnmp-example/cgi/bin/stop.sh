#!/bin/bash

PROCESS='php-fpm'

ps -ef | grep ${PROCESS} | grep -v grep | awk '{print $2}' | xargs -n 1 kill -9