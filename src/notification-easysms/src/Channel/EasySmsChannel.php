<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Notification\EasySms\Channel;

use FriendsOfHyperf\Notification\Contract\Channel as ChannelContract;
use FriendsOfHyperf\Notification\EasySms\Contract\Smsable;
use FriendsOfHyperf\Notification\EasySms\EasySms;
use FriendsOfHyperf\Notification\Notification;
use Overtrue\EasySms\Message;
use RuntimeException;

class EasySmsChannel implements ChannelContract
{
    public function __construct(
        private readonly EasySms $easySms
    ) {
    }

    public function send(mixed $notifiable, Notification $notification,?string $transportName = null): mixed
    {
        return $this->easySms->send(
            $notifiable->routeNotificationFor('sms', $notification),
            $this->buildPayload($notifiable, $notification)
        );
    }

    protected function buildPayload(mixed $notifiable, Notification $notification): array|Message
    {
        if ($notification instanceof Smsable) {
            return $notification->toSms($notifiable);
        }

        throw new RuntimeException('Notifications do not implement the toSms contract.');
    }
}
