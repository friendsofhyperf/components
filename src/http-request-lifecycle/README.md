# Http Request LifeCycle

[![Latest Stable Version](https://poser.pugx.org/friendsofhyperf/http-request-lifecycle/version.png)](https://packagist.org/packages/friendsofhyperf/http-request-lifecycle)
[![Total Downloads](https://poser.pugx.org/friendsofhyperf/http-request-lifecycle/d/total.png)](https://packagist.org/packages/friendsofhyperf/http-request-lifecycle)
[![GitHub license](https://img.shields.io/github/license/friendsofhyperf/http-request-lifecycle)](https://github.com/friendsofhyperf/http-request-lifecycle)

Http Request LifeCycle component for hyperf.

## Installation

```bash
composer require friendsofhyperf/Http-request-lifecycle
```

## Usage

```php
<?php
namespace App\Listener;

use FriendsOfHyperf\HttpRequestLifeCycle\Events\RequestHandled;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;

/**
 * @Listener
 */
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
