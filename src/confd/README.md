# Confd

The Confd component fetches configuration from Etcd or Nacos, maps remote values to environment
variable names, and can write them to an existing `.env` file or watch for changes.

## Installation

Install the component and the package required by the driver you use:

```shell
composer require friendsofhyperf/confd
composer require hyperf/etcd
# or
composer require hyperf/nacos
```

Etcd and Nacos are optional driver dependencies. Nacos v2 gRPC APIs additionally require
`google/protobuf`, `hyperf/grpc`, and `hyperf/http2-client`. Decoding YAML values requires the PHP
YAML extension.

Publish the configuration file:

```shell
php bin/hyperf.php vendor:publish friendsofhyperf/confd
```

## Configuration

The published file is `config/autoload/confd.php`. Its main options are:

| Option | Description |
| --- | --- |
| `default` | Driver name. Defaults to `etcd` and can be set with `CONFD_DRIVER`. |
| `drivers.<name>.driver` | Driver class implementing `DriverInterface`. |
| `drivers.<name>.mapping` | Maps remote configuration paths to environment variable names. |
| `env_path` | Existing `.env` file updated by `confd:env`. |
| `watch` | Starts watching on server boot. Defaults to `true` and can be set with `CONFD_WATCH`. |
| `watches` | Environment variable names that trigger `WatchDispatched`. |
| `interval` | Polling interval in seconds. Defaults to `1` and can be set with `CONFD_INTERVAL`. |

### Etcd

The Etcd driver fetches keys under `namespace` and returns only keys listed in `mapping`. Each
mapping key is an Etcd key, and each value is the environment variable name returned by `fetch()`.

```php
'etcd' => [
    'driver' => FriendsOfHyperf\Confd\Driver\Etcd::class,
    'client' => [
        'uri' => env('ETCD_URI', ''),
        'version' => 'v3beta',
        'timeout' => 10,
    ],
    'namespace' => '/test',
    'mapping' => [
        '/mysql/host' => 'DB_HOST',
        '/mysql/port' => 'DB_PORT',
    ],
],
```

### Nacos

The Nacos driver reads every entry in `listener_config`, decodes it according to `type`, and then
resolves dot-notated paths from `mapping`. Supported types are `json`, `yml`/`yaml`, and `xml`;
other or omitted types remain strings.

```php
'nacos' => [
    'driver' => FriendsOfHyperf\Confd\Driver\Nacos::class,
    'client' => [
        'host' => '127.0.0.1',
        'port' => 8848,
        'username' => 'nacos',
        'password' => 'nacos',
        'guzzle' => [
            'config' => ['timeout' => 3, 'connect_timeout' => 1],
        ],
        'grpc' => [
            'enable' => false,
            'heartbeat' => 10,
        ],
    ],
    'listener_config' => [
        'mysql' => [
            'tenant' => 'framework',
            'data_id' => 'mysql',
            'group' => 'DEFAULT_GROUP',
            'type' => 'json',
        ],
    ],
    'mapping' => [
        'mysql.host' => 'DB_HOST',
        'mysql.charset' => 'DB_CHARSET',
    ],
],
```

When `client.grpc.enable` is `false`, Nacos polls at `interval`. When it is `true`, the driver
registers gRPC listeners for the entries in `listener_config`.

## Updating the Environment File

Fetch the mapped values from the selected driver and update the configured existing `.env` file:

```shell
php bin/hyperf.php confd:env
```

Use `--env-path` (or `-E`) to override `confd.env_path`:

```shell
php bin/hyperf.php confd:env --env-path=/path/to/.env
```

The command returns exit code `1` when the file does not exist or fetching/writing fails.

## Public API

`FriendsOfHyperf\Confd\Confd` exposes:

- `fetch(): array`: fetches mapped environment-variable values from the selected driver.
- `watch(): void`: performs an initial fetch and starts the selected driver's watch loop.

Custom drivers must implement `FriendsOfHyperf\Confd\Driver\DriverInterface`, which declares
`fetch(): array` and `loop(callable $callback): void`. The built-in `Noop` driver returns no values
and does not start a loop.

## Watching and Events

When `confd.watch` is enabled, `WatchOnBootListener` calls `Confd::watch()` on
`MainWorkerStart` or `MainCoroutineServerStart`.

During the watch loop, a changed existing value dispatches `ConfigChanged`. If the changed
environment variable name is also listed in `confd.watches`, `WatchDispatched` is dispatched too.
Both events expose public array properties:

- `ConfigChanged`: `$current`, `$previous`, and `$changes`.
- `WatchDispatched`: `$changes`.

```php
<?php

namespace App\Listener;

use FriendsOfHyperf\Confd\Event\ConfigChanged;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;

#[Listener]
class ConfigChangedListener implements ListenerInterface
{
    public function listen(): array
    {
        return [ConfigChanged::class];
    }

    public function process(object $event): void
    {
        foreach ($event->changes as $key => $value) {
            // React to the changed mapped environment value.
        }
    }
}
```

## Supported Drivers

- Etcd
- Nacos
- Noop
