# Telescope Elasticsearch Driver

This driver allows you to switch from an SQL database to Elasticsearch as the data storage backend, eliminating deadlocks and making Telescope a production-ready logging system.

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