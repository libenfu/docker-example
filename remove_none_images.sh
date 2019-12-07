#!/bin/bash

# 删除容器
docker container rm $(docker container ls -a -q)

# 删除镜像
docker images | grep none | awk '{print $3}' | xargs -n 1 docker rmi
