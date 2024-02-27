# Middleware Plus

[![Latest Stable Version](https://poser.pugx.org/friendsofhyperf/middleware-plus/version.png)](https://packagist.org/packages/friendsofhyperf/middleware-plus)
[![Total Downloads](https://poser.pugx.org/friendsofhyperf/middleware-plus/d/total.png)](https://packagist.org/packages/friendsofhyperf/middleware-plus)
[![GitHub license](https://img.shields.io/github/license/friendsofhyperf/middleware-plus)](https://github.com/friendsofhyperf/middleware-plus)

The middleware plus component for Hyperf.

## Installation

```shell
composer require friendsofhyperf/middleware-plus
```

## Usage

- Define a middleware

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

- Set middleware in route

```php
use App\Middleware\FooMiddleware;

Router::addRoute(['GET', 'POST', 'HEAD'], '/', 'App\Controller\IndexController::index', [
    'middleware' => [
        FooMiddleware::class . ':1,2,3',
    ],
]);
```

- Set middleware alias

```php
// config/autoload/dependencies.php

return [
    'foo-middleware' => App\Middleware\FooMiddleware::class,
];
```

- Set middleware in route using alias

```php
use App\Middleware\FooMiddleware;

Router::addRoute(['GET', 'POST', 'HEAD'], '/', 'App\Controller\IndexController::index', [
    'middleware' => [
        'foo-middleware:1,2,3',
    ],
]);
```

## Contact

- [Twitter](https://twitter.com/huangdijia)
- [Gmail](mailto:huangdijia@gmail.com)

## License

[MIT](LICENSE)
