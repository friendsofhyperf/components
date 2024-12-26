# Http Logger

為 Hyperf 提供的 HTTP 日誌元件。

## 安裝

```shell
composer require "friendsofhyperf/http-logger
```

## 釋出配置

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
