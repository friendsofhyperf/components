# Telescope

## 可用監聽器

- [x] 請求監視器
- [x] 異常監視器
- [x] 數據查詢監視器
- [x] gRPC請求監視器
- [x] Redis監視器
- [x] 日誌監視器
- [x] 命令行監視器
- [x] 事件監視器
- [x] HTTP Client 監視器
- [x] 緩存監視器
- [x] 定時任務監視器

## 安裝

```shell
composer require friendsofhyperf/telescope:~3.1.0
```

使用 `vendor:publish`  命令來發布其公共資源

```shell
php bin/hyperf.php vendor:publish friendsofhyperf/telescope
```

運行 `migrate` 命令執行數據庫變更來創建和保存 Telescope 需要的數據

```shell
php bin/hyperf.php migrate
```

## 使用

### 中間件（可選，僅用於gRPC）

在 `config/autoload/middlewares.php`配置文件加上中間件

如需gRPC的額外功能，請使用`grpc`中間件

```php
<?php

return [
    'grpc' => [
        FriendsOfHyperf\Telescope\Middleware\TelescopeMiddleware::class,
    ],
];
```

> 注意: 請求追蹤功能已通過 RequestHandledListener 自動啟用。TelescopeMiddleware 僅用於 gRPC 的額外功能。

## 查看儀表板

`http://127.0.0.1:9501/telescope`

## 數據庫配置

在 `config/autoload/telescope.php`管理數據庫連接配置，默認使用`default`連接

```php
'connection' => env('TELESCOPE_DB_CONNECTION', 'default'),
```

## 標籤

您可能希望將自己的自定義標籤附加到條目。為此，您可以使用 **`Telescope::tag`**  方法。

## 批量過濾

您可能只想記錄某些特殊條件下的條目。為此，您可以使用 **`Telescope::filter`** 方法。

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
