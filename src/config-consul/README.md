# config-consul

[![Latest Stable Version](https://img.shields.io/packagist/v/friendsofhyperf/config-consul)](https://packagist.org/packages/friendsofhyperf/config-consul)
[![Total Downloads](https://img.shields.io/packagist/dt/friendsofhyperf/config-consul)](https://packagist.org/packages/friendsofhyperf/config-consul)
[![License](https://img.shields.io/packagist/l/friendsofhyperf/config-consul)](https://github.com/friendsofhyperf/config-consul)

The consul config component for Hyperf.

## Installation

```shell
composer require friendsofhyperf/config-consul
```

## Configure

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

## Contact

- [Twitter](https://twitter.com/huangdijia)
- [Gmail](mailto:huangdijia@gmail.com)

## License

[MIT](LICENSE)
