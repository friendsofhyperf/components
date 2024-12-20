# Telescope Elasticsearch Driver

It will allow you to switch from an SQL database to Elasticsearch as a driver for your data storage, and it will eliminate deadlocks, making Telescope a ready-for-production logging system.

## Installation

```shell
composer require friendsofhyperf/telescope-elasticsearch
```

## Publish Config

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
