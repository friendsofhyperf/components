# Notification EasySms Channel

此組件通過 [EasySms](https://github.com/overtrue/easy-sms) 發送通知。

## 安裝

```shell
composer require friendsofhyperf/notification-easysms
```

組件依賴 `friendsofhyperf/notification` 和 `overtrue/easy-sms:^3.0`，Composer 會自動安裝。

## 配置

發佈 `config/autoload/easy_sms.php`，然後參照 EasySms 文檔配置默認策略、默認網關和網關憑據：

```shell
php bin/hyperf.php vendor:publish friendsofhyperf/notification-easysms
```

組件會使用完整的 `easy_sms` 配置數組創建 `EasySms` 實例。

## 使用

應用啓動時，組件會以 `easy-sms` 通道名註冊 `EasySmsChannel`。

### 定義通知路由

在接收者中使用 `Notifiable` trait，並定義 `routeNotificationForSms()`。該方法應返回手機號碼字符串。

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

雖然通知通道名為 `easy-sms`，但它會通過 `routeNotificationFor('sms', $notification)` 獲取接收者，
因此實際調用的方法是 `routeNotificationForSms()`。

### 創建短信通知

通知必須實現 `Smsable`。`via()` 應返回 `easy-sms`，`toSms()` 應返回 EasySms 接受的數組或
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
            'content' => "您的驗證碼是 {$this->code}。",
            'template' => 'SMS_123456789',
            'data' => [
                'code' => $this->code,
            ],
        ];
    }
}
```

EasySms 會將數組載荷轉換為 `Message`。支持的消息屬性包括 `content`、`template`、`data`、`type`
和 `gateways`。需要通過 `setGateways()` 等方法配置消息時，可直接返回 `Message`：

```php
public function toSms(mixed $notifiable): array|Message
{
    return (new Message())
        ->setTemplate('SMS_123456789')
        ->setData(['code' => $this->code])
        ->setGateways(['aliyun']);
}
```

消息未指定網關時，EasySms 會使用 `config/autoload/easy_sms.php` 中的 `default.gateways`。發送結果是
EasySms 返回的網關結果數組，並會傳給通知調度器。如果通知未實現 `Smsable`，通道會拋出
`RuntimeException`。
