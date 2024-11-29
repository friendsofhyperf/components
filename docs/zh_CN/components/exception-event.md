# exception-event

## 安装

```shell
composer require friendsofhyperf/exception-event
```

## 使用

### 定义监听器

```php
<?php

namespace App\Listener;

use FriendsOfHyperf\ExceptionEvent\Event\ExceptionEvent;

class ExceptionEventListener implements ListenerInterface
{
    public function listen(): array
    {
        return [
            ExceptionEvent::class,
        ];
    }

    /**
     * @param ExceptionEvent|object $event
     */
    public function process(object $event)
    {
        $exception = $event->getException();
        $message = sprintf('Exception: %s in %s:%s', $exception->getMessage(), $exception->getFile(), $exception->getLine());
        $event->getLogger()->error($message);
    }
}
```
