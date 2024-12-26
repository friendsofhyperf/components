# Config Consul

The consul config component for Hyperf.

## 安裝

```shell
composer require friendsofhyperf/config-consul
```

## 配置

```php
// config/autoload/config_center.php

return [
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
