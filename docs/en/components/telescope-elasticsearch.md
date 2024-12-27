# Telescope Elasticsearch Driver

It allows you to switch from an SQL database to Elasticsearch as a data storage driver, and it eliminates deadlocks, making Telescope a log system suitable for a production environment.

## Installation

```shell
composer require friendsofhyperf/telescope-elasticsearch
```

## Publish Configuration

```shell
php bin/hyperf.php vendor:publish friendsofhyperf/telescope --id=config
```

## Configuration

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