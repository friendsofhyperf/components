# Notification EasySms Channel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/friendsofhyperf/notification-easysms.svg?style=flat-square)](https://packagist.org/packages/friendsofhyperf/notification-easysms)
[![Total Downloads](https://img.shields.io/packagist/dt/friendsofhyperf/notification-easysms.svg?style=flat-square)](https://packagist.org/packages/friendsofhyperf/notification-easysms)
[![GitHub license](https://img.shields.io/github/license/friendsofhyperf/notification-easysms)](https://github.com/friendsofhyperf/notification-easysms)

## Installation

```shell
composer require friendsofhyperf/notification-easysms:~3.1.0
```

## Usage

### Use `Notifiable` trait in Model

```php
<?php

declare(strict_types=1);

namespace App\Model;

use Hyperf\DbConnection\Model\Model;
use FriendsOfHyperf\Notification\Traits\Notifiable;
use Overtrue\EasySms\PhoneNumber;

/**
 * @property int $id 
 * @property \Carbon\Carbon $created_at 
 * @property \Carbon\Carbon $updated_at 
 */
class User extends Model
{
    use Notifiable;

    /**
     * The table associated with the model.
     */
    protected ?string $table = 'user';

    // 通知手机号
    public function routeNotificationForSms(): string|PhoneNumber
    {
        return $this->phone;
    }
}
```

### SMS Notifications

- Install the easy-sms package

```shell
composer require overtrue/easy-sms:^3.0
```

```php
namespace App\Notification;

use FriendsOfHyperf\Notification\EasySms\Contract\EasySmsChannelToSmsArrayContract;
use FriendsOfHyperf\Notification\EasySms\Contract\Smsable;
use FriendsOfHyperf\Notification\Notification;
use Overtrue\EasySms\Message;

// 通知类
class TestNotification extends Notification implements Smsable
{
    public function __construct(private string $code)
    {
    }
    
    public function via()
    {
        return [
            'easy-sms'
        ];
    }
    
    /**
     * 返回的内容将组装到短信模型中 new Message($notification->toSms()). 
     * 文档 https://github.com/overtrue/easy-sms?tab=readme-ov-file#%E5%AE%9A%E4%B9%89%E7%9F%AD%E4%BF%A1 
     */
    public function toSms(mixed $notifiable): array|Message
    {
        return [
            'code' => $this->code,
            'template' => 'SMS_123456789',
            'data' => [
                'code' => $this->code,
            ]
        ];
    }

    // or return customer Message
    // public function toSms(mixed $notifiable): array|Message
    // {
    //     return new Message();
    // }

}
```
