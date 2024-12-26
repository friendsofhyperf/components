# Exception Event

## 安裝

```shell
composer require friendsofhyperf/exception-event
```

## 使用

### 定義監聽器

```php
<?php

namespace App\Listener;

use FriendsOfHyperf\ExceptionEvent\Event\ExceptionDispatched;

class ExceptionEventListener implements ListenerInterface
{
    public function listen(): array
    {
        return [
            ExceptionDispatched::class,
        ];
    }

    /**
     * @param ExceptionDispatched|object $event
     */
    public function process(object $event)
    {
        $exception = $event->throwable;
        $message = sprintf('Exception: %s in %s:%s', $exception->getMessage(), $exception->getFile(), $exception->getLine());
        $event->getLogger()->error($message);
    }
}
```
