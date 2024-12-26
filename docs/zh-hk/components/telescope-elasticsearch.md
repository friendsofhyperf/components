# Telescope Elasticsearch Driver

它允許您從 SQL 數據庫切換到 Elasticsearch 作為數據存儲的驅動程序，並且它將消除死鎖，使 Telescope 成為一個可用於生產環境的日誌系統。

## 安裝

```shell
composer require friendsofhyperf/telescope-elasticsearch
```

## 發佈配置

```shell
php bin/hyperf.php vendor:publish friendsofhyperf/telescope --id=config
```

## 配置

```php
// config/autoload/telescope.php
return [
    'driver' => 'elasticsearch',
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
