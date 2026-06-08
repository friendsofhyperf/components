# Notification EasySms Channel

此元件透過 [EasySms](https://github.com/overtrue/easy-sms) 傳送通知。

## 安裝

```shell
composer require friendsofhyperf/notification-easysms
```

元件依賴 `friendsofhyperf/notification` 和 `overtrue/easy-sms:^3.0`，Composer 會自動安裝。

## 設定

發佈 `config/autoload/easy_sms.php`，然後參照 EasySms 文件設定預設策略、預設閘道和閘道憑證：

```shell
php bin/hyperf.php vendor:publish friendsofhyperf/notification-easysms
```

元件會使用完整的 `easy_sms` 設定陣列建立 `EasySms` 實例。

## 使用

應用程式啟動時，元件會以 `easy-sms` 通道名稱註冊 `EasySmsChannel`。

### 定義通知路由

在接收者中使用 `Notifiable` trait，並定義 `routeNotificationForSms()`。此方法應傳回電話號碼字串。

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

雖然通知通道名稱為 `easy-sms`，但它會透過 `routeNotificationFor('sms', $notification)` 取得接收者，
因此實際呼叫的方法是 `routeNotificationForSms()`。

### 建立短訊通知

通知必須實作 `Smsable`。`via()` 應傳回 `easy-sms`，`toSms()` 應傳回 EasySms 接受的陣列或
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
            'content' => "你的驗證碼是 {$this->code}。",
            'template' => 'SMS_123456789',
            'data' => [
                'code' => $this->code,
            ],
        ];
    }
}
```

EasySms 會將陣列載荷轉換為 `Message`。支援的訊息屬性包括 `content`、`template`、`data`、`type`
和 `gateways`。需要透過 `setGateways()` 等方法設定訊息時，可直接傳回 `Message`：

```php
public function toSms(mixed $notifiable): array|Message
{
    return (new Message())
        ->setTemplate('SMS_123456789')
        ->setData(['code' => $this->code])
        ->setGateways(['aliyun']);
}
```

訊息未指定閘道時，EasySms 會使用 `config/autoload/easy_sms.php` 中的 `default.gateways`。傳送結果是
EasySms 傳回的閘道結果陣列，並會傳給通知調度器。如果通知未實作 `Smsable`，通道會拋出
`RuntimeException`。
