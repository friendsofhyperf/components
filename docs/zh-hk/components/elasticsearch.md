# Elasticsearch

一個為 Hyperf 定製的 Elasticsearch 客户端組件。

## 安裝

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
