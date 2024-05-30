# Notification

[![Latest Version on Packagist](https://img.shields.io/packagist/v/friendsofhyperf/notification.svg?style=flat-square)](https://packagist.org/packages/friendsofhyperf/notification)
[![Total Downloads](https://img.shields.io/packagist/dt/friendsofhyperf/notification.svg?style=flat-square)](https://packagist.org/packages/friendsofhyperf/notification)
[![GitHub license](https://img.shields.io/github/license/friendsofhyperf/notification)](https://github.com/friendsofhyperf/notification)

## Installation

```shell
composer require friendsofhyperf/notification:~3.1.0
```

## Usage

### Model use `Notifiable` trait

```php
<?php

declare(strict_types=1);

namespace App\Model;

use Hyperf\DbConnection\Model\Model;
use FriendsOfHyperf\Notification\Traits\Notifiable;use Overtrue\EasySms\PhoneNumber;

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

    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = ['id', 'created_at', 'updated_at'];

    /**
     * The attributes that should be cast to native types.
     */
    protected array $casts = ['id' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime'];
    
    // 通知手机号
    public function routeNotificationForSms(): string|PhoneNumber
    {
        return $this->phone;
    }
}
```

### Database Notifications

```shell
# Install the database package
composer require hyperf/database:~3.1.0

# Publish the migration file
php bin/hyperf.php notification:table

# Run the migration
php bin/hyperf.php migrate

# Create a notification
php bin/hyperf.php make:notification TestNotification
```

---

```php
<?php

namespace App\Notification;

use FriendsOfHyperf\Notification\Notification;

class TestNotification extends Notification
{
    public function __construct(
        private string $message
    ){}

    public function via()
    {
        return [
           // database channel
            'database'
        ];
    }

    public function toDatabase(mixed $notifiable): array
    {
        return [
            'message' => $this->message,
        ];
    }
}
```

---

```php
// Your controller or service
// 通知一条消息
$user->notify(new TestNotification('系统通知:xxx'));
$noReadCount = $user->unreadNotifications()->count();
$this->output->success('发送成功,未读消息数:' . $noReadCount);
$notifications = $user->unreadNotifications()->first();
$this->output->success('消息内容:' . $notifications->data['message']);
$notifications->markAsRead();
$noReadCount = $user->unreadNotifications()->count();
$this->output->success('标记已读,未读消息数:' . $noReadCount);
```

### SMS Notifications

```shell
# Install the easy-sms package
composer require overtrue/easy-sms:~3.0
```

---

```php
// config/autoload/dependencies.php
return [
    Overtrue\EasySms\EasySms::class => App\Factory\EasySmsFactory::class,
];
```

---

```php
namespace App\Notification;

use FriendsOfHyperf\Notification\Notification;
use Overtrue\EasySms\Message;

## 通知类
class TestNotification extends Notification
{
    public function __construct(private string $code)
    {
    }
    
    public function via()
    {
        // SMS channel
        return [
            'sms'
        ];
    }
    
    /**
    * 短信模型文档: https://github.com/overtrue/easy-sms?tab=readme-ov-file#%E5%AE%9A%E4%B9%89%E7%9F%AD%E4%BF%A1
    * 此处返回的是短信模型、如果存在此方法则会调用此方法组装数据 
    * @return Message
    */
    public function toSmsMessage(): Message
    {
        
    }
    
    /**
    * 返回的内容将组装到短信模型中 new Message($notification->toSms()). 
    * 文档 https://github.com/overtrue/easy-sms?tab=readme-ov-file#%E5%AE%9A%E4%B9%89%E7%9F%AD%E4%BF%A1 
    */
    public function toSms(mixed $notifiable): array
    {
        return [
            'code' => $this->code,
            'template' => 'SMS_123456789',
            'data' => [
                'code' => $this->code,
            ]
        ];
    }
    // 如果不想使用toSmsMessage方法可以直接返回数组，返回内容将会作为 easySms->send 的第二个参数直接发送
    public function toArray(){
        return  [
            'content'  => '您的验证码为: 6379',
            'template' => 'SMS_001',
            'data' => [
                'code' => 6379
            ],
        ];
    }
}
```

### Symfony Notifications

Send notifications using Symfony Notifier.

Email, SMS, Slack, Telegram, etc.

```shell
composer require symfony/notifier
```

#### Email

```shell
composer require symfony/mailer
```

---

```php
<?php
// app/Factory/Notifier.php
namespace App\Factory;

use Hyperf\Contract\StdoutLoggerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Notifier\Channel\ChannelInterface;
use Symfony\Component\Notifier\Channel\EmailChannel;
use function Hyperf\Support\env;

class Notifier
{
    public function __construct(
        protected EventDispatcherInterface $dispatcher,
        protected StdoutLoggerInterface $logger,
    )
    {
    }

    public function __invoke()
    {
        return new \Symfony\Component\Notifier\Notifier($this->channels());
    }

    /**
     * @return ChannelInterface[]
     */
    public function channels(): array
    {
        return [
            'email' =>  new EmailChannel(
                transport: Transport::fromDsn(
                   // MAIL_DSN=smtp://user:password@localhost:1025
                    env('MAIL_DSN'),
                    dispatcher: $this->dispatcher,
                    logger: $this->logger
                ),
                from: 'root@imoi.cn'
            ),
        ];
    }
}
```

```php
<?php
// app/Notification/TestNotification.php
namespace App\Notification;

use App\Model\User;
use FriendsOfHyperf\Notification\Notification;
use Symfony\Component\Notifier\Recipient\Recipient;

class TestNotification extends Notification
{
    public function __construct(
        private string $message
    ){}

    public function via()
    {
        return [
            'symfony'
        ];
    }

    public function toSymfony(User $user)
    {
        return (new \Symfony\Component\Notifier\Notification\Notification($this->message,['email']))->content('The introduction to the notification.');
    }

    public function toRecipient(User $user)
    {
        return new Recipient('2771717608@qq.com');
    }


}
```

```php
<?php
// config/autoload/dependencies.php
declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
return [
    \Symfony\Component\Notifier\NotifierInterface::class => \App\Factory\Notifier::class
];

```


#### Usage in controller

```php
$user = User::create();
// 通知一条消息
$user->notify(new TestNotification('系统通知:xxx'));
```