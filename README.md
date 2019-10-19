# docker-example

## 常用镜像

1. centos 为添加了常用依赖的版本, `bccq/centos`
2. php-build 为源码编译版的 PHP, 使用的是 7.3.10 版本, 支持 fpm, `bccq/php-build`
3. php-bundle 为支持 swoole 的 PHP, 使用 php 7.3 搭配 swoole 4.4.5 版本, `bccq/php-bundle`

## 实例

### network 的本地组网

参考 1.network-example, 其中进行网络隔离分组.

### lamp 服务网

参考 2.lnmp-example, 将 nginx, swoole, mysql, redis, nodejs 进行组网, 实现一键搭建系统.
