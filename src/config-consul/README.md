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

## Donate

> If you like them, Buy me a cup of coffee.

| Alipay | WeChat | Buy Me A Coffee |
|  ----  |  ----  |  ----  |
| <img src="https://hdj.me/images/alipay-min.jpg" width="200" height="200" />  | <img src="https://hdj.me/images/wechat-pay-min.jpg" width="200" height="200" /> | <img src="https://hdj.me/images/bmc_qr.jpg" width="200" height="200" /> |

<a href="https://www.buymeacoffee.com/huangdijiag" target="_blank"><img src="https://cdn.buymeacoffee.com/buttons/v2/default-yellow.png" alt="Buy Me A Coffee" style="height: 60px !important;width: 217px !important;" ></a>

## Contact

- [Twitter](https://twitter.com/huangdijia)
- [Gmail](mailto:huangdijia@gmail.com)

## License

[MIT](LICENSE)
