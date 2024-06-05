# Notification Mail Channel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/friendsofhyperf/notification-easysms.svg?style=flat-square)](https://packagist.org/packages/friendsofhyperf/notification-easysms)
[![Total Downloads](https://img.shields.io/packagist/dt/friendsofhyperf/notification-easysms.svg?style=flat-square)](https://packagist.org/packages/friendsofhyperf/notification-easysms)
[![GitHub license](https://img.shields.io/github/license/friendsofhyperf/notification-easysms)](https://github.com/friendsofhyperf/notification-easysms)

## Installation

```shell
composer require friendsofhyperf/notification-mail:~3.1.0
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
    public function routeNotificationForMail(): string|PhoneNumber
    {
        return $this->mail;
    }
}
```

### Mail Notifications

```shell
php bin/hyperf.php gen:markdown-mail Test
```

output

```php

namespace App\Mail;

use FriendsOfHyperf\Notification\Mail\Message\MailMessage;
use FriendsOfHyperf\Notification\Notification;

class Test extends Notification
{
    /**
     * Create a new notification instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)->from('xxx@xxx.cn','Hyperf')->replyTo('xxx@qq.com','zds')->markdown('email');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }

}
```


```php
// storage/view/email.blade.php
xxx
```
