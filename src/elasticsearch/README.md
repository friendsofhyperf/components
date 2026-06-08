# Elasticsearch

This component integrates the official Elasticsearch PHP client with Hyperf's Guzzle client and
provides a static facade for configured connections.

## Requirements

The package targets Hyperf 3.2 and requires `elasticsearch/elasticsearch` version 8 or 9,
`hyperf/config`, `hyperf/context`, and `hyperf/guzzle`. It does not declare optional dependencies.

## Installation

```shell
composer require friendsofhyperf/elasticsearch
php bin/hyperf.php vendor:publish friendsofhyperf/elasticsearch
```

The publish command creates `config/autoload/elasticsearch.php`.

## Configuration

The published configuration defines the `default` connection. `ELASTICSEARCH_HOST` is split on
commas, so it may contain multiple hosts. Additional named connections may be added under
`elasticsearch`:

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

For facade connections, `hosts` may be an array or a comma-separated string. The facade only reads
the `hosts` key; configure other client behavior through `ClientBuilderFactory` and the upstream
builder.

## Usage

### Client Builder Factory

`ClientBuilderFactory::create(array $options = [])` passes `$options` to Hyperf's
`GuzzleClientFactory` and returns an unbuilt upstream `ClientBuilder`. Configure the builder before
calling `build()`:

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

The factory does not read `config/autoload/elasticsearch.php`; hosts and other builder settings must
be applied explicitly.

### Facade

Static facade calls use the `default` connection. Use `connection()` to select a named connection:

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

Each facade call builds a new client. If the selected connection is missing or `null`, the component
throws `InvalidArgumentException` with the connection name.

## Upstream API

Client methods, request parameters, response objects, authentication, and additional builder options
are provided by the installed `elasticsearch/elasticsearch` version. Refer to the matching upstream
client documentation when using them.
