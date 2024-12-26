# Elasticsearch

The Elasticsearch client for Hyperf.

## 安装

```shell
composer require friendsofhyperf/elasticsearch
```

## 使用

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
