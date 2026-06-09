# Notification EasySms Channel

[English](README.md)

此组件通过 [EasySms](https://github.com/overtrue/easy-sms) 发送通知。

## 安装

```shell
composer require friendsofhyperf/notification-easysms
```

组件依赖 `friendsofhyperf/notification` 和 `overtrue/easy-sms:^3.0`，Composer 会自动安装。

## 配置

发布 `config/autoload/easy_sms.php`，然后参照 EasySms 文档配置默认策略、默认网关和网关凭据：

```shell
php bin/hyperf.php vendor:publish friendsofhyperf/notification-easysms
```

组件会使用完整的 `easy_sms` 配置数组创建 `EasySms` 实例。

## 使用

应用启动时，组件会以 `easy-sms` 通道名注册 `EasySmsChannel`。

### 定义通知路由

在接收者中使用 `Notifiable` trait，并定义 `routeNotificationForSms()`。该方法应返回手机号码字符串。

```php
<?php

declare(strict_types=1);

namespace App\Model;

use FriendsOfHyperf\Notification\Traits\Notifiable;
use Hyperf\DbConnection\Model\Model;

class User extends Model
{
    use Notifiable;

    public function routeNotificationForSms(): string
    {
        return $this->phone;
    }
}
```

虽然通知通道名为 `easy-sms`，但它会通过 `routeNotificationFor('sms', $notification)` 获取接收者，
因此实际调用的方法是 `routeNotificationForSms()`。

### 创建短信通知

通知必须实现 `Smsable`。`via()` 应返回 `easy-sms`，`toSms()` 应返回 EasySms 接受的数组或
`Overtrue\EasySms\Message`。

```php
<?php

declare(strict_types=1);

namespace App\Notification;

use FriendsOfHyperf\Notification\EasySms\Contract\Smsable;
use FriendsOfHyperf\Notification\Notification;
use Overtrue\EasySms\Message;

class VerificationCodeNotification extends Notification implements Smsable
{
    public function __construct(private string $code)
    {
    }

    public function via(object $notifiable): array
    {
        return ['easy-sms'];
    }

    public function toSms(mixed $notifiable): array|Message
    {
        return [
            'content' => "您的验证码是 {$this->code}。",
            'template' => 'SMS_123456789',
            'data' => [
                'code' => $this->code,
            ],
        ];
    }
}
```

EasySms 会将数组载荷转换为 `Message`。支持的消息属性包括 `content`、`template`、`data`、`type`
和 `gateways`。需要通过 `setGateways()` 等方法配置消息时，可直接返回 `Message`：

```php
public function toSms(mixed $notifiable): array|Message
{
    return (new Message())
        ->setTemplate('SMS_123456789')
        ->setData(['code' => $this->code])
        ->setGateways(['aliyun']);
}
```

消息未指定网关时，EasySms 会使用 `config/autoload/easy_sms.php` 中的 `default.gateways`。发送结果是
EasySms 返回的网关结果数组，并会传给通知调度器。如果通知未实现 `Smsable`，通道会抛出
`RuntimeException`。
