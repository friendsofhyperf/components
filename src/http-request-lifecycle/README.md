# Http Request LifeCycle

[![Latest Stable Version](https://img.shields.io/packagist/v/friendsofhyperf/http-request-lifecycle)](https://packagist.org/packages/friendsofhyperf/http-request-lifecycle)
[![Total Downloads](https://img.shields.io/packagist/dt/friendsofhyperf/http-request-lifecycle)](https://packagist.org/packages/friendsofhyperf/http-request-lifecycle)
[![License](https://img.shields.io/packagist/l/friendsofhyperf/http-request-lifecycle)](https://github.com/friendsofhyperf/http-request-lifecycle)

Http Request LifeCycle component for Hyperf.

## Installation

```bash
composer require friendsofhyperf/http-request-lifecycle
```

## Usage

```php
<?php
namespace App\Listener;

use FriendsOfHyperf\HttpRequestLifeCycle\Events\RequestHandled;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;

#[Listener]
class RequestHandledListener implements ListenerInterface
{
    public function listen(): array
    {
        return [
            RequestHandled::class,
        ];
    }

    /**
     * @param RequestHandled $event
     */
    public function process(object $event)
    {
        var_dump($event);
    }
}
```

## Events

- [x] RequestReceived
- [x] RequestHandled
- [x] RequestTerminated
