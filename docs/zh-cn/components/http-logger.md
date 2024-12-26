# Http Logger

为 Hyperf 提供的 HTTP 日志组件。

## 安装

```shell
composer require "friendsofhyperf/http-logger
```

## 发布配置

```shell
php bin/hyperf.php vendor:publish friendsofhyperf/http-logger
```

## 使用

```php
return [
    'http' => [
        \FriendsOfHyperf\Http\Logger\Middleware\HttpLogger::class,
    ],
];
```
