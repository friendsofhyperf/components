# Hyperf config-consul

[![Latest Stable Version](https://poser.pugx.org/friendsofhyperf/config-consul/version.png)](https://packagist.org/packages/friendsofhyperf/config-consul)
[![Total Downloads](https://poser.pugx.org/friendsofhyperf/config-consul/d/total.png)](https://packagist.org/packages/friendsofhyperf/config-consul)
[![GitHub license](https://img.shields.io/github/license/friendsofhyperf/config-consul)](https://github.com/friendsofhyperf/config-consul)

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
