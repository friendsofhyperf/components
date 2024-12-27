# Notification

## Installation

```shell
composer require friendsofhyperf/notification:~3.1.0
```

## Usage

### Using the `Notifiable` trait in a Model

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
    
    // Notification phone number
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
// Send a notification
$user->notify(new TestNotification('System Message: xxx'));
$noReadCount = $user->unreadNotifications()->count();
$this->output->success('Sent successfully. Unread messages count: ' . $noReadCount);
$notifications = $user->unreadNotifications()->first();
$this->output->success('Message content: ' . $notifications->data['message']);
$notifications->markAsRead();
$noReadCount = $user->unreadNotifications()->count();
$this->output->success('Marked as read. Unread messages count: ' . $noReadCount);
```

### SMS Notifications

- [notification-easy-sms](https://github.com/friendsofhyperf/notification-easysms)

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

#### Usage in Controller

```php
$user = User::create();
// Send a notification
$user->notify(new TestNotification('System Message: xxx'));
```