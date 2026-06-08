# Confd

Confd 组件从 Etcd 或 Nacos 获取配置，将远程值映射为环境变量名，并可将其写入
已有的 `.env` 文件或监听变更。

## 安装

安装组件以及所用驱动需要的包：

```shell
composer require friendsofhyperf/confd
composer require hyperf/etcd
# or
composer require hyperf/nacos
```

Etcd 和 Nacos 是可选驱动依赖。Nacos v2 gRPC API 还需要 `google/protobuf`、`hyperf/grpc`
和 `hyperf/http2-client`。解码 YAML 值需要 PHP YAML 扩展。

发布配置文件：

```shell
php bin/hyperf.php vendor:publish friendsofhyperf/confd
```

## 配置

发布的文件为 `config/autoload/confd.php`，主要选项如下：

| 选项 | 说明 |
| --- | --- |
| `default` | 驱动名称。默认为 `etcd`，可通过 `CONFD_DRIVER` 设置。 |
| `drivers.<name>.driver` | 实现 `DriverInterface` 的驱动类。 |
| `drivers.<name>.mapping` | 将远程配置路径映射到环境变量名。 |
| `env_path` | `confd:env` 更新的已有 `.env` 文件。 |
| `watch` | 在服务器启动时开始监听。默认为 `true`，可通过 `CONFD_WATCH` 设置。 |
| `watches` | 会触发 `WatchDispatched` 的环境变量名。 |
| `interval` | 轮询间隔秒数。默认为 `1`，可通过 `CONFD_INTERVAL` 设置。 |

### Etcd

Etcd 驱动获取 `namespace` 下的键，并只返回 `mapping` 中列出的键。每个映射键是 Etcd
键，每个映射值是 `fetch()` 返回的环境变量名。

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

Nacos 驱动读取 `listener_config` 中的每个条目，根据 `type` 解码，再从 `mapping`
解析点号路径。支持的类型为 `json`、`yml`/`yaml` 和 `xml`；其他类型或未指定类型时
保留为字符串。

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

当 `client.grpc.enable` 为 `false` 时，Nacos 按 `interval` 轮询；为 `true` 时，驱动为
`listener_config` 中的条目注册 gRPC 监听。

## 更新环境文件

从选定驱动获取映射值，并更新配置的已有 `.env` 文件：

```shell
php bin/hyperf.php confd:env
```

使用 `--env-path`（或 `-E`）覆盖 `confd.env_path`：

```shell
php bin/hyperf.php confd:env --env-path=/path/to/.env
```

文件不存在或获取/写入失败时，命令返回退出码 `1`。

## 公开 API

`FriendsOfHyperf\Confd\Confd` 提供：

- `fetch(): array`：从选定驱动获取映射后的环境变量值。
- `watch(): void`：执行首次获取并启动选定驱动的监听循环。

自定义驱动必须实现 `FriendsOfHyperf\Confd\Driver\DriverInterface`，其中声明了
`fetch(): array` 和 `loop(callable $callback): void`。内置 `Noop` 驱动不返回值，
也不启动循环。

## 监听与事件

启用 `confd.watch` 时，`WatchOnBootListener` 会在 `MainWorkerStart` 或
`MainCoroutineServerStart` 时调用 `Confd::watch()`。

监听循环中，已有值发生变更时会派发 `ConfigChanged`。如果变更的环境变量名也列在
`confd.watches` 中，还会派发 `WatchDispatched`。两个事件都提供公开数组属性：

- `ConfigChanged`：`$current`、`$previous` 和 `$changes`。
- `WatchDispatched`：`$changes`。

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

## 支持的驱动

- Etcd
- Nacos
- Noop
