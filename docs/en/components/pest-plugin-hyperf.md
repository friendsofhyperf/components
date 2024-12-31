# Pest Plugin Hyperf

> This is a [Pest](https://pestphp.com) plugin that enables your Hyperf project's Pest to run in a Swoole-based coroutine environment.

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

- Configure test/prepend.php

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