# Notification EasySms Channel

This component sends notifications through [EasySms](https://github.com/overtrue/easy-sms).

## Installation

```shell
composer require friendsofhyperf/notification-easysms
```

The component requires `friendsofhyperf/notification` and `overtrue/easy-sms:^3.0`;
Composer installs them automatically.

## Configuration

Publish `config/autoload/easy_sms.php`, then configure its default strategy, default gateways, and
gateway credentials according to the EasySms documentation:

```shell
php bin/hyperf.php vendor:publish friendsofhyperf/notification-easysms
```

The component constructs its `EasySms` instance from the complete `easy_sms` configuration array.

## Usage

When the application boots, the component registers `EasySmsChannel` under the `easy-sms`
channel name.

### Define the Notification Route

Use the `Notifiable` trait on the recipient and define `routeNotificationForSms()`. Return the
phone number as a string.

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

Although the notification channel is named `easy-sms`, it resolves the recipient by calling
`routeNotificationFor('sms', $notification)`, which invokes `routeNotificationForSms()`.

### Create an SMS Notification

The notification must implement `Smsable`. Return `easy-sms` from `via()` and return either an
array accepted by EasySms or an `Overtrue\EasySms\Message` from `toSms()`.

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
            'content' => "Your verification code is {$this->code}.",
            'template' => 'SMS_123456789',
            'data' => [
                'code' => $this->code,
            ],
        ];
    }
}
```

EasySms converts an array payload to a `Message`. Supported message attributes include
`content`, `template`, `data`, `type`, and `gateways`. Return a `Message` directly when you need
to configure it with methods such as `setGateways()`:

```php
public function toSms(mixed $notifiable): array|Message
{
    return (new Message())
        ->setTemplate('SMS_123456789')
        ->setData(['code' => $this->code])
        ->setGateways(['aliyun']);
}
```

When the message does not select gateways, EasySms uses `default.gateways` from
`config/autoload/easy_sms.php`. Sending returns EasySms's gateway result array to the notification
dispatcher. If the notification does not implement `Smsable`, the channel throws a
`RuntimeException`.
