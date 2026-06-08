# Config Consul

A Consul configuration center driver for Hyperf.

## Installation

```shell
composer require friendsofhyperf/config-consul
```

The package installs its Hyperf Config Center, Consul, codec, and stringable dependencies automatically.

## Configuration

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

| Option | Description |
| --- | --- |
| `enable` | Enables Hyperf Config Center. |
| `driver` | Selects the `consul` driver configuration. |
| `drivers.consul.driver` | The driver class. Use `ConsulDriver::class`. |
| `drivers.consul.packer` | Unpacks each mapped value. Defaults to `JsonPacker::class`. |
| `drivers.consul.client` | Optional Consul client settings. Supports `uri` and `token`. |
| `drivers.consul.namespaces` | An array of Consul KV prefixes to fetch recursively. |
| `drivers.consul.mapping` | Maps normalized Consul keys to Hyperf configuration keys. Only mapped keys are applied. |
| `drivers.consul.interval` | Pull interval in seconds. Defaults to `5`. |

If `client` is omitted or empty, the component reuses Hyperf's bound
`Hyperf\Consul\KVInterface` client. When `client` is configured, `uri` defaults to
`http://127.0.0.1:8500`, a non-empty `token` is sent as the `X-Consul-Token` header, and the
HTTP timeout is 2 seconds.

## Behavior

- Every namespace is requested with Consul's recursive option. If namespaces return the same key, the
  value from the later namespace wins before mapping.
- Consul KV values are Base64-decoded, then passed to the configured packer. With `JsonPacker`, stored
  values must contain valid JSON after Base64 decoding.
- Consul keys are normalized to start with `/` before lookup in `mapping`.
- The package registers `FriendsOfHyperf\ConfigConsul\ClientInterface` and
  `FriendsOfHyperf\ConfigConsul\Consul\KVInterface` in the container. `ConsulDriver` is the Config
  Center driver entry point.
