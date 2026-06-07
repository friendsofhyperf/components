# Config Consul

適用於 Hyperf 的 Consul 配置中心引擎。

## 安裝

```shell
composer require friendsofhyperf/config-consul
```

該套件會自動安裝所需的 Hyperf 設定中心、Consul、編解碼和字串處理依賴。

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

| 設定項 | 說明 |
| --- | --- |
| `enable` | 啟用 Hyperf 設定中心。 |
| `driver` | 選擇 `consul` 驅動設定。 |
| `drivers.consul.driver` | 驅動類別，應使用 `ConsulDriver::class`。 |
| `drivers.consul.packer` | 解包每個映射值，預設為 `JsonPacker::class`。 |
| `drivers.consul.client` | 選用的 Consul 用戶端設定，支援 `uri` 和 `token`。 |
| `drivers.consul.namespaces` | 要遞迴拉取的 Consul KV 前綴陣列。 |
| `drivers.consul.mapping` | 將正規化後的 Consul 鍵映射到 Hyperf 設定鍵；只有已映射的鍵會被套用。 |
| `drivers.consul.interval` | 拉取間隔，單位為秒，預設為 `5`。 |

省略 `client` 或將其設為空時，元件會重用 Hyperf 容器中綁定的
`Hyperf\Consul\KVInterface` 用戶端。設定 `client` 時，`uri` 預設為
`http://127.0.0.1:8500`；非空 `token` 會透過 `X-Consul-Token` 請求標頭傳送；HTTP 逾時時間為
2 秒。

## 行為

- 每個命名空間都會使用 Consul 的遞迴選項請求。如果多個命名空間傳回相同的鍵，映射前以後一個命名空間的值為準。
- Consul KV 值會先進行 Base64 解碼，再交給設定的 packer。使用 `JsonPacker` 時，Base64 解碼後的儲存值必須是有效 JSON。
- Consul 鍵在 `mapping` 查找前會被正規化為以 `/` 開頭。
- 該套件會在容器中註冊 `FriendsOfHyperf\ConfigConsul\ClientInterface` 和
  `FriendsOfHyperf\ConfigConsul\Consul\KVInterface`；`ConsulDriver` 是設定中心驅動入口。
