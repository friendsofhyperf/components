# Telescope

## 可用监听器

- [x] 请求监视器
- [x] 异常监视器
- [x] 数据查询监视器
- [x] gRPC请求监视器
- [x] Redis监视器
- [x] 日志监视器
- [x] 命令行监视器
- [x] 事件监视器
- [x] HTTP Client 监视器
- [x] 缓存监视器
- [x] 定时任务监视器

## 安装

```shell
composer require friendsofhyperf/telescope:~3.1.0
```

使用 `vendor:publish`  命令来发布其公共资源

```shell
php bin/hyperf.php vendor:publish friendsofhyperf/telescope
```

运行 `migrate` 命令执行数据库变更来创建和保存 Telescope 需要的数据

```shell
php bin/hyperf.php migrate
```

## 使用

### 中间件（可选，仅用于gRPC）

在 `config/autoload/middlewares.php`配置文件加上中间件

如需gRPC的额外功能，请使用`grpc`中间件

```php
<?php

return [
    'grpc' => [
        FriendsOfHyperf\Telescope\Middleware\TelescopeMiddleware::class,
    ],
];
```

> 注意: 请求跟踪功能已通过 RequestHandledListener 自动启用。TelescopeMiddleware 仅用于 gRPC 的额外功能。

## 查看仪表板

`http://127.0.0.1:9501/telescope`

## 数据库配置

在 `config/autoload/telescope.php`管理数据库连接配置，默认使用`default`连接

```php
'connection' => env('TELESCOPE_DB_CONNECTION', 'default'),
```

## 标签

您可能希望将自己的自定义标签附加到条目。为此，您可以使用 **`Telescope::tag`**  方法。

## 批量过滤

您可能只想记录某些特殊条件下的条目。为此，您可以使用 **`Telescope::filter`** 方法。

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
