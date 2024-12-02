# Elasticsearch

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
