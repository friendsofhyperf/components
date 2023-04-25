# config-consul

[![Latest Stable Version](https://img.shields.io/packagist/v/friendsofhyperf/config-consul)](https://packagist.org/packages/friendsofhyperf/config-consul)
[![Total Downloads](https://img.shields.io/packagist/dt/friendsofhyperf/config-consul)](https://packagist.org/packages/friendsofhyperf/config-consul)
[![License](https://img.shields.io/packagist/l/friendsofhyperf/config-consul)](https://github.com/friendsofhyperf/config-consul)

The consul config component for Hyperf.

## Installation

```shell
composer require friendsofhyperf/config-consul:^3.0
```

## Configure

```php
// config/autoload/config_center.php

return [
    'drivers' => [
        'consul' => [
            'driver' => FriendsOfHyperf\ConfigConsul\ConsulDriver::class,
            'packer' => Hyperf\Codec\Packer\JsonPacker::class,
            'uri' => env('CONSUL_URI'),
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

## Sponsor

If you like this project, Buy me a cup of coffee. [ [Alipay](https://hdj.me/images/alipay.jpg) | [WePay](https://hdj.me/images/wechat-pay.jpg) ]
