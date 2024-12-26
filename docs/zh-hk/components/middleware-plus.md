# Middleware Plus

The middleware plus component for Hyperf.

## 安裝

```shell
composer require friendsofhyperf/middleware-plus
```

## 使用

- 定義中間件

```php
<?php

declare(strict_types=1);

namespace App\Middleware;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class FooMiddleware implements MiddlewareInterface
{
    public function __construct(protected ContainerInterface $container)
    {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler, $a = null, $b = null, $c = null): ResponseInterface
    {
        var_dump($a, $b, $c);
        return $handler->handle($request);
    }
}

```

- 在路由中設置中間件

```php
use App\Middleware\FooMiddleware;

Router::addRoute(['GET', 'POST', 'HEAD'], '/', 'App\Controller\IndexController::index', [
    'middleware' => [
        FooMiddleware::class . ':1,2,3',
    ],
]);
```

- 設置中間件別名

```php
// config/autoload/dependencies.php

return [
    'foo-middleware' => App\Middleware\FooMiddleware::class,
];
```

- 使用中間件別名

```php
use App\Middleware\FooMiddleware;

Router::addRoute(['GET', 'POST', 'HEAD'], '/', 'App\Controller\IndexController::index', [
    'middleware' => [
        'foo-middleware:1,2,3',
    ],
]);
```
