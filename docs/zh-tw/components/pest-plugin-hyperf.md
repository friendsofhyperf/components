# Pest Plugin Hyperf

> 這是一個 [Pest](https://pestphp.com) 外掛，使您的 Hyperf 專案的 Pest 能夠在基於 Swoole 的協程環境中執行。

## 安裝

```shell
composer require friendsofhyperf/pest-plugin-hyperf --dev
```

## 使用

```shell
php vendor/bin/pest --coroutine
# or
php vendor/bin/pest --prepend test/prepend.php --coroutine
```

- 配置 test/prepend.php

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
