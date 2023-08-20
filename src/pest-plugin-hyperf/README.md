# pest-plugin-hyperf

[![Latest Stable Version](https://img.shields.io/packagist/v/friendsofhyperf/pest-plugin-hyperf)](https://packagist.org/packages/friendsofhyperf/pest-plugin-hyperf)
[![Total Downloads](https://img.shields.io/packagist/dt/friendsofhyperf/pest-plugin-hyperf)](https://packagist.org/packages/friendsofhyperf/pest-plugin-hyperf)
[![License](https://img.shields.io/packagist/l/friendsofhyperf/pest-plugin-hyperf)](https://github.com/friendsofhyperf/pest-plugin-hyperf)

> This is a [Pest](https://pestphp.com) plugin that enables your Hyperf project's Pest to run within a Swoole-based coroutine environment.

## Installation

```shell
composer require friendsofhyperf/pest-plugin-hyperf --dev
```

## Usage

```shell
php vendor/bin/pest --coroutine
# or
php vendor/bin/pest --prepend test/prepend.php --coroutine
```

- test/prepend.php

```php
<?php
require_once __DIR__ . '/../vendor/autoload.php';

defined('BASE_PATH') or define('BASE_PATH', dirname(__DIR__, 1));

(function () {
    \Hyperf\Di\ClassLoader::init();

    \Hyperf\Context\ApplicationContext::setContainer(
        new \Hyperf\Di\Container((new \Hyperf\Di\Definition\DefinitionSourceFactory())())
    );
    
    // $container->get(Hyperf\Contract\ApplicationInterface::class);
})();

```

## Donate

> If you like them, Buy me a cup of coffee.

| Alipay | WeChat |
|  ----  |  ----  |
| <img src="https://hdj.me/images/alipay-min.jpg" width="200" height="200" />  | <img src="https://hdj.me/images/wechat-pay-min.jpg" width="200" height="200" /> |

## Contact

- [Twitter](https://twitter.com/huangdijia)
- [Gmail](mailto:huangdijia@gmail.com)

## License

[MIT](LICENSE)
