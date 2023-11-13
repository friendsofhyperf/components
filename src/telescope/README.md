# Hyperf-telescope

hyperf 版本的望远镜

## 功能点

- [x] 记录request请求
- [x] 记录异常错误
- [x] 记录sql语句
- [x] 记录grpc service请求
- [x] 记录redis
- [x] 记录log
- [x] 记录command
- [x] 记录event
- [x] 记录http client

## 安装组件

```shell
composer require guandeng/hyperf-telescope:dev-main
```

## 发布配置

```shell
php bin/hyperf.php vendor:publish guandeng/hyperf-telescope
```

## 添加监听器(请求端)

```php
<?php

// config/autoload/listeners.php

return [
    FriendsOfHyperf\Telescope\Listener\RequestHandledListener::class,
];

```

## 添加中间件

```php
<?php

// config/autoload/middlewares.php

return [
    'grpc' => [
        FriendsOfHyperf\Telescope\Middleware\TelescopeMiddleware::class,
    ],
];

```

> TelescopeMiddleware 与 RequestHandledListener，二选一即可。

## 修改.env

```env
# telescope
TELESCOPE_DB_CONNECTION=default

TELESCOPE_ENABLE_REQUEST=true
TELESCOPE_ENABLE_COMMAND=true
TELESCOPE_ENABLE_GRPC=true
TELESCOPE_ENABLE_LOG=true
TELESCOPE_ENABLE_REDIS=true
TELESCOPE_ENABLE_EVENT=true
TELESCOPE_ENABLE_EXCEPTION=true
TELESCOPE_ENABLE_JOB=true
TELESCOPE_ENABLE_DB=true
TELESCOPE_ENABLE_GUZZLE=true

TELESCOPE_SERVER_ENABLE=true
```

## 访问地址

`http://127.0.0.1:9509/telescope/requests`

<img src="./requests.jpg">
<img src="./grpc.jpg">
<img src="./exception.jpg">
