# Confd

Confd 元件從 Etcd 或 Nacos 獲取配置，將遠端值對映為環境變數名，並可將其寫入
已有的 `.env` 檔案或監聽變更。

## 安裝

安裝元件以及所用驅動需要的包：

```shell
composer require friendsofhyperf/confd
composer require hyperf/etcd
# or
composer require hyperf/nacos
```

Etcd 和 Nacos 是可選驅動依賴。Nacos v2 gRPC API 還需要 `google/protobuf`、`hyperf/grpc`
和 `hyperf/http2-client`。解碼 YAML 值需要 PHP YAML 擴充套件。

釋出配置檔案：

```shell
php bin/hyperf.php vendor:publish friendsofhyperf/confd
```

## 配置

釋出的檔案為 `config/autoload/confd.php`，主要選項如下：

| 選項 | 說明 |
| --- | --- |
| `default` | 驅動名稱。預設為 `etcd`，可透過 `CONFD_DRIVER` 設定。 |
| `drivers.<name>.driver` | 實現 `DriverInterface` 的驅動類。 |
| `drivers.<name>.mapping` | 將遠端配置路徑對映到環境變數名。 |
| `env_path` | `confd:env` 更新的已有 `.env` 檔案。 |
| `watch` | 在伺服器啟動時開始監聽。預設為 `true`，可透過 `CONFD_WATCH` 設定。 |
| `watches` | 會觸發 `WatchDispatched` 的環境變數名。 |
| `interval` | 輪詢間隔秒數。預設為 `1`，可透過 `CONFD_INTERVAL` 設定。 |

### Etcd

Etcd 驅動獲取 `namespace` 下的鍵，並只返回 `mapping` 中列出的鍵。每個對映鍵是 Etcd
鍵，每個對映值是 `fetch()` 返回的環境變數名。

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

Nacos 驅動讀取 `listener_config` 中的每個條目，根據 `type` 解碼，再從 `mapping`
解析點號路徑。支援的型別為 `json`、`yml`/`yaml` 和 `xml`；其他型別或未指定型別時
保留為字串。

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

當 `client.grpc.enable` 為 `false` 時，Nacos 按 `interval` 輪詢；為 `true` 時，驅動為
`listener_config` 中的條目註冊 gRPC 監聽。

## 更新環境檔案

從選定驅動獲取對映值，並更新配置的已有 `.env` 檔案：

```shell
php bin/hyperf.php confd:env
```

使用 `--env-path`（或 `-E`）覆蓋 `confd.env_path`：

```shell
php bin/hyperf.php confd:env --env-path=/path/to/.env
```

檔案不存在或獲取/寫入失敗時，命令返回退出碼 `1`。

## 公開 API

`FriendsOfHyperf\Confd\Confd` 提供：

- `fetch(): array`：從選定驅動獲取對映後的環境變數值。
- `watch(): void`：執行首次獲取並啟動選定驅動的監聽迴圈。

自定義驅動必須實現 `FriendsOfHyperf\Confd\Driver\DriverInterface`，其中聲明瞭
`fetch(): array` 和 `loop(callable $callback): void`。內建 `Noop` 驅動不返回值，
也不啟動迴圈。

## 監聽與事件

啟用 `confd.watch` 時，`WatchOnBootListener` 會在 `MainWorkerStart` 或
`MainCoroutineServerStart` 時呼叫 `Confd::watch()`。

監聽迴圈中，已有值發生變更時會派發 `ConfigChanged`。如果變更的環境變數名也列在
`confd.watches` 中，還會派發 `WatchDispatched`。兩個事件都提供公開陣列屬性：

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

## 支援的驅動

- Etcd
- Nacos
- Noop
