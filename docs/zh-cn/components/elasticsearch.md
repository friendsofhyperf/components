# Elasticsearch

此组件将 Elasticsearch 官方 PHP 客户端与 Hyperf 的 Guzzle 客户端集成，并为已配置的连接提供静态
Facade。

## 要求

此包适用于 Hyperf 3.2，并依赖 8 或 9 版本的 `elasticsearch/elasticsearch`、`hyperf/config`、
`hyperf/context` 和 `hyperf/guzzle`。它没有声明可选依赖。

## 安装

```shell
composer require friendsofhyperf/elasticsearch
php bin/hyperf.php vendor:publish friendsofhyperf/elasticsearch
```

发布命令会创建 `config/autoload/elasticsearch.php`。

## 配置

发布的配置定义了 `default` 连接。`ELASTICSEARCH_HOST` 会按逗号拆分，因此可以包含多个主机。可以在
`elasticsearch` 下添加其他命名连接：

```php
return [
    'default' => [
        'hosts' => explode(',', env('ELASTICSEARCH_HOST', '')),
    ],
    'analytics' => [
        'hosts' => ['http://127.0.0.1:9200'],
    ],
];
```

对于 Facade 连接，`hosts` 可以是数组或逗号分隔的字符串。Facade 只读取 `hosts` 键；其他客户端行为
需要通过 `ClientBuilderFactory` 和上游 Builder 配置。

## 使用

### 客户端 Builder 工厂

`ClientBuilderFactory::create(array $options = [])` 会将 `$options` 传给 Hyperf 的
`GuzzleClientFactory`，并返回尚未构建的上游 `ClientBuilder`。请先配置 Builder，再调用 `build()`：

```php
use FriendsOfHyperf\Elasticsearch\ClientBuilderFactory;

class Foo
{
    public function __construct(protected ClientBuilderFactory $clientBuilderFactory)
    {
    }

    public function handle()
    {
        $client = $this->clientBuilderFactory
            ->create(['timeout' => 5])
            ->setHosts(['http://127.0.0.1:9200'])
            ->build();

        return $client->info();
    }
}
```

此工厂不会读取 `config/autoload/elasticsearch.php`；必须显式设置主机和其他 Builder 选项。

### Facade

Facade 静态调用使用 `default` 连接。使用 `connection()` 选择命名连接：

```php
use FriendsOfHyperf\Elasticsearch\Facade\Elasticsearch;

$info = Elasticsearch::info();
$results = Elasticsearch::connection('analytics')->search([
    'index' => 'articles',
    'body' => [
        'query' => [
            'match_all' => (object) [],
        ],
    ],
]);
```

每次 Facade 调用都会构建一个新客户端。如果所选连接缺失或为 `null`，组件会抛出包含连接名称的
`InvalidArgumentException`。

## 上游 API

客户端方法、请求参数、响应对象、身份验证和其他 Builder 选项由已安装的
`elasticsearch/elasticsearch` 版本提供。使用这些功能时，请查阅对应版本的上游客户端文档。
