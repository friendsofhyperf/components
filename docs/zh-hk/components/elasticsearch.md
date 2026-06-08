# Elasticsearch

此組件將 Elasticsearch 官方 PHP 客戶端與 Hyperf 的 Guzzle 客戶端整合，並為已配置的連線提供靜態
Facade。

## 要求

此套件適用於 Hyperf 3.2，並依賴 8 或 9 版本的 `elasticsearch/elasticsearch`、`hyperf/config`、
`hyperf/context` 和 `hyperf/guzzle`。它沒有聲明可選依賴。

## 安裝

```shell
composer require friendsofhyperf/elasticsearch
php bin/hyperf.php vendor:publish friendsofhyperf/elasticsearch
```

發佈命令會建立 `config/autoload/elasticsearch.php`。

## 配置

發佈的配置定義了 `default` 連線。`ELASTICSEARCH_HOST` 會按逗號拆分，因此可以包含多個主機。可以在
`elasticsearch` 下加入其他命名連線：

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

對於 Facade 連線，`hosts` 可以是陣列或逗號分隔的字串。Facade 只讀取 `hosts` 鍵；其他客戶端行為
需要透過 `ClientBuilderFactory` 和上游 Builder 配置。

## 使用

### 客戶端 Builder 工廠

`ClientBuilderFactory::create(array $options = [])` 會將 `$options` 傳給 Hyperf 的
`GuzzleClientFactory`，並傳回尚未構建的上游 `ClientBuilder`。請先配置 Builder，再呼叫 `build()`：

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

此工廠不會讀取 `config/autoload/elasticsearch.php`；必須明確設定主機和其他 Builder 選項。

### Facade

Facade 靜態呼叫使用 `default` 連線。使用 `connection()` 選擇命名連線：

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

每次 Facade 呼叫都會構建一個新客戶端。如果所選連線缺失或為 `null`，組件會拋出包含連線名稱的
`InvalidArgumentException`。

## 上游 API

客戶端方法、請求參數、回應物件、身份驗證和其他 Builder 選項由已安裝的
`elasticsearch/elasticsearch` 版本提供。使用這些功能時，請查閱對應版本的上游客戶端文件。
