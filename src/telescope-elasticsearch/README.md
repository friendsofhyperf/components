# Telescope Elasticsearch Driver

[![Latest Version](https://img.shields.io/packagist/v/friendsofhyperf/telescope-elasticsearch.svg?style=flat-square)](https://packagist.org/packages/friendsofhyperf/telescope-elasticsearch)
[![Total Downloads](https://img.shields.io/packagist/dt/friendsofhyperf/telescope-elasticsearch.svg?style=flat-square)](https://packagist.org/packages/friendsofhyperf/telescope-elasticsearch)
[![GitHub license](https://img.shields.io/github/license/friendsofhyperf/telescope-elasticsearch)](https://github.com/friendsofhyperf/telescope-elasticsearch)

It will allow you to switch from an SQL database to Elasticsearch as a driver for your data storage, and it will eliminate deadlocks, making Telescope a ready-for-production logging system.

## Installation

```shell
composer require friendsofhyperf/telescope-elasticsearch
```

## Configuration

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
