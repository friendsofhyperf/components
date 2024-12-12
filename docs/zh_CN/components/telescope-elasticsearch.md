# Telescope Elasticsearch Driver

它允许您从 SQL 数据库切换到 Elasticsearch 作为数据存储的驱动程序，并且它将消除死锁，使 Telescope 成为一个可用于生产环境的日志系统。

## 安装

```shell
composer require friendsofhyperf/telescope-elasticsearch
```

## 配置

```php
// config/autoload/telescope.php
return [
    'storage' => [
        'elasticsearch' => [
            'driver' => FriendsOfHyperf\TelescopeElasticsearch\Storage\ElasticsearchEntriesRepository::class,
            'index' => 'telescope_entries',

            'hosts' => ['127.0.0.1'],
            'username' => null,
            'password' => null,
        ],
    ],
];
```
