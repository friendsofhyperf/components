# config-consul

[![Latest Stable Version](https://img.shields.io/packagist/v/friendsofhyperf/config-consul)](https://packagist.org/packages/friendsofhyperf/config-consul)
[![Total Downloads](https://img.shields.io/packagist/dt/friendsofhyperf/config-consul)](https://packagist.org/packages/friendsofhyperf/config-consul)
[![License](https://img.shields.io/packagist/l/friendsofhyperf/config-consul)](https://github.com/friendsofhyperf/config-consul)

The config center component for Hyperf.

## Installation

~~~base
composer require friendsofhyperf/config-consul
~~~

## Configure

~~~php
// config/autoload/config_center.php

return [
    'drivers' => [
        'consul' => [
            'driver' => FriendsOfHyperf\ConfigConsul\ConsulDriver::class,
            'packer' => Hyperf\Utils\Packer\JsonPacker::class,
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
~~~
