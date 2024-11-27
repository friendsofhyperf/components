# Telescope

An elegant debug assistant for the hyperf framework.

## 功能支持

- [x] request
- [x] exception
- [x] sql
- [x] grpc server/client
- [x] redis
- [x] log
- [x] command
- [x] event
- [x] guzzle
- [x] cache
- [x] rpc server/client

## 安装

```shell
composer require friendsofhyperf/telescope:~3.1.0
```

## 发布配置

```shell
php bin/hyperf.php vendor:publish friendsofhyperf/telescope
```

## 数据库迁移

```shell
php bin/hyperf.php migrate
```

## 添加监听器

```php
<?php

// config/autoload/listeners.php

return [
    FriendsOfHyperf\Telescope\Listener\RequestHandledListener::class,
];

```

## 添加中间件

```php
<?php

// config/autoload/middlewares.php

return [
    'grpc' => [
        FriendsOfHyperf\Telescope\Middleware\TelescopeMiddleware::class,
    ],
];

```

> TelescopeMiddleware or RequestHandledListener, you can choose one of them.

## 设置环境变量

```env
# telescope
TELESCOPE_DB_CONNECTION=default

TELESCOPE_ENABLE_REQUEST=true
TELESCOPE_ENABLE_COMMAND=true
TELESCOPE_ENABLE_GRPC=true
TELESCOPE_ENABLE_LOG=true
TELESCOPE_ENABLE_REDIS=true
TELESCOPE_ENABLE_EVENT=true
TELESCOPE_ENABLE_EXCEPTION=true
TELESCOPE_ENABLE_JOB=true
TELESCOPE_ENABLE_DB=true
TELESCOPE_ENABLE_GUZZLE=true
TELESCOPE_ENABLE_CACHE=true
TELESCOPE_ENABLE_RPC=true

TELESCOPE_SERVER_ENABLE=true
```

## 访问

`http://127.0.0.1:9509/telescope/requests`

<img src="https://raw.githubusercontent.com/friendsofhyperf/telescope/main/requests.jpg" />

<img src="https://github.com/friendsofhyperf/telescope/raw/main/grpc.jpg" />

<img src="https://github.com/friendsofhyperf/telescope/raw/main/exception.jpg" />

## 标签

您可能希望为条目附加自定义标签。为此，您可以使用 **`Telescope::tag`** 方法。

## 过滤器

您可能只想在某些特殊条件下记录条目。为此，您可以使用 **`Telescope::filter`** 方法。

例子

```php
use FriendsOfHyperf\Telescope\Telescope;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BootApplication;
use FriendsOfHyperf\Telescope\IncomingEntry;

class TelescopeInitListener implements ListenerInterface
{
    public function listen(): array
    {
        return [
            BootApplication::class,
        ];
    }

    public function process(object $event): void
    {
        // attach your own custom tags
        Telescope::tag(function (IncomingEntry $entry) {
            if ($entry->type === 'request') {
                return [
                    'status:' . $entry->content['response_status'],
                    'uri:'. $entry->content['uri'],
                ];
            }
        });

        // filter entry
        Telescope::filter(function (IncomingEntry $entry): bool {
            if ($entry->type === 'request'){
                if ($entry->content['uri'] == 'xxxx') {
                    return false;
                }
            }
            return true;
        });

    }
}
```

> You can also do this in middleware.
