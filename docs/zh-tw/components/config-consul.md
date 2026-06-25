# Config Consul

適用於 Hyperf 的 Consul 配置中心引擎。

## 安裝

```shell
composer require friendsofhyperf/config-consul
```

該包會自動安裝所需的 Hyperf 配置中心、Consul、編解碼和字串處理依賴。

## 配置

```php
// config/autoload/config_center.php

return [
    'enable' => true,
    'driver' => 'consul',
    'drivers' => [
        'consul' => [
            'driver' => FriendsOfHyperf\ConfigConsul\ConsulDriver::class,
            'packer' => Hyperf\Codec\Packer\JsonPacker::class,
            'client' => [
                'uri' => env('CONSUL_URI'),
                'token' => env('CONSUL_TOKEN'),
            ],
            'namespaces' => [
                '/application',
            ],
            'mapping' => [
                // consul key => config key
                '/application/test' => 'test',
            ],
            'interval' => 5,
        ],
    ],
];
```

| 配置項 | 說明 |
| --- | --- |
| `enable` | 啟用 Hyperf 配置中心。 |
| `driver` | 選擇 `consul` 驅動配置。 |
| `drivers.consul.driver` | 驅動類，應使用 `ConsulDriver::class`。 |
| `drivers.consul.packer` | 解包每個對映值，預設為 `JsonPacker::class`。 |
| `drivers.consul.client` | 可選的 Consul 客戶端配置，支援 `uri` 和 `token`。 |
| `drivers.consul.namespaces` | 要遞迴拉取的 Consul KV 字首陣列。 |
| `drivers.consul.mapping` | 將規範化後的 Consul 鍵對映到 Hyperf 配置鍵；只有已對映的鍵會被應用。 |
| `drivers.consul.interval` | 拉取間隔，單位為秒，預設為 `5`。 |

省略 `client` 或將其設為空時，元件會複用 Hyperf 容器中繫結的
`Hyperf\Consul\KVInterface` 客戶端。配置 `client` 時，`uri` 預設為
`http://127.0.0.1:8500`；非空 `token` 會透過 `X-Consul-Token` 請求頭髮送；HTTP 超時時間為
2 秒。

## 行為

- 每個名稱空間都會使用 Consul 的遞迴選項請求。如果多個名稱空間返回相同的鍵，對映前以後一個名稱空間的值為準。
- Consul KV 值會先進行 Base64 解碼，再交給配置的 packer。使用 `JsonPacker` 時，Base64 解碼後的儲存值必須是有效 JSON。
- Consul 鍵在 `mapping` 查詢前會被規範化為以 `/` 開頭。
- 該包會在容器中註冊 `FriendsOfHyperf\ConfigConsul\ClientInterface` 和
  `FriendsOfHyperf\ConfigConsul\Consul\KVInterface`；`ConsulDriver` 是配置中心驅動入口。
