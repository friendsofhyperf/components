# Http Logger

The http logger component for Hyperf.

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
