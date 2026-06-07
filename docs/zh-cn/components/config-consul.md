# Config Consul

适用于 Hyperf 的 Consul 配置中心引擎。

## 安装

```shell
composer require friendsofhyperf/config-consul
```

该包会自动安装所需的 Hyperf 配置中心、Consul、编解码和字符串处理依赖。

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

| 配置项 | 说明 |
| --- | --- |
| `enable` | 启用 Hyperf 配置中心。 |
| `driver` | 选择 `consul` 驱动配置。 |
| `drivers.consul.driver` | 驱动类，应使用 `ConsulDriver::class`。 |
| `drivers.consul.packer` | 解包每个映射值，默认为 `JsonPacker::class`。 |
| `drivers.consul.client` | 可选的 Consul 客户端配置，支持 `uri` 和 `token`。 |
| `drivers.consul.namespaces` | 要递归拉取的 Consul KV 前缀数组。 |
| `drivers.consul.mapping` | 将规范化后的 Consul 键映射到 Hyperf 配置键；只有已映射的键会被应用。 |
| `drivers.consul.interval` | 拉取间隔，单位为秒，默认为 `5`。 |

省略 `client` 或将其设为空时，组件会复用 Hyperf 容器中绑定的
`Hyperf\Consul\KVInterface` 客户端。配置 `client` 时，`uri` 默认为
`http://127.0.0.1:8500`；非空 `token` 会通过 `X-Consul-Token` 请求头发送；HTTP 超时时间为
2 秒。

## 行为

- 每个命名空间都会使用 Consul 的递归选项请求。如果多个命名空间返回相同的键，映射前以后一个命名空间的值为准。
- Consul KV 值会先进行 Base64 解码，再交给配置的 packer。使用 `JsonPacker` 时，Base64 解码后的存储值必须是有效 JSON。
- Consul 键在 `mapping` 查找前会被规范化为以 `/` 开头。
- 该包会在容器中注册 `FriendsOfHyperf\ConfigConsul\ClientInterface` 和
  `FriendsOfHyperf\ConfigConsul\Consul\KVInterface`；`ConsulDriver` 是配置中心驱动入口。
