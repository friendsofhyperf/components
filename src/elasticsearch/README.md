# Elasticsearch

[![Latest Stable Version](https://img.shields.io/packagist/v/friendsofhyperf/elasticsearch)](https://packagist.org/packages/friendsofhyperf/elasticsearch)
[![Total Downloads](https://img.shields.io/packagist/dt/friendsofhyperf/elasticsearch)](https://packagist.org/packages/friendsofhyperf/elasticsearch)
[![License](https://img.shields.io/packagist/l/friendsofhyperf/elasticsearch)](https://github.com/friendsofhyperf/elasticsearch)

The Elasticsearch client for Hyperf.

## Installation

```shell
composer require friendsofhyperf/elasticsearch
```

## Usage

```php
use FriendsOfHyperf\Elasticsearch\ClientBuilderFactory;

class Foo
{
    public function __construct(protected ClientBuilderFactory $clientBuilderFactory)
    {
    }

    public function handle()
    {
        $client = $clientBuilderFactory->create()->build();
        // ...
    }
}
```

## Contact

- [Twitter](https://twitter.com/huangdijia)
- [Gmail](mailto:huangdijia@gmail.com)

## License

[MIT](LICENSE)
