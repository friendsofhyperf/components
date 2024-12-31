# Notification EasySms Channel

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

    // Notification phone number
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

// Notification class
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
     * The returned content will be assembled into the SMS model new Message($notification->toSms()). 
     * Documentation https://github.com/overtrue/easy-sms?tab=readme-ov-file#%E5%AE%9A%E4%B9%89%E7%9F%AD%E4%BF%A1 
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

    // or return custom Message
    // public function toSms(mixed $notifiable): array|Message
    // {
    //     return new Message();
    // }

}
```