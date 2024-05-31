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
use FriendsOfHyperf\Notification\EasySms\Contract\EasySmsChannelToSmsArrayContract;
use FriendsOfHyperf\Notification\EasySms\Contract\EasySmsChannelToSmsContract;
use FriendsOfHyperf\Notification\EasySms\Contract\EasySmsChannelToSmsMessageContract;
use FriendsOfHyperf\Notification\Notification;
use Overtrue\EasySms\EasySms;
use Overtrue\EasySms\Message;
use RuntimeException;

class EasySmsChannel implements ChannelContract
{
    public function __construct(
        private readonly EasySms $easySms
    ) {
    }

    public function send(mixed $notifiable, Notification $notification): mixed
    {
        return $this->easySms->send(
            $notifiable->routeNotificationFor('sms', $notification),
            $this->buildPayload($notifiable, $notification)
        );
    }

    protected function buildPayload(mixed $notifiable, Notification $notification): array|Message
    {
        if ($notification instanceof EasySmsChannelToSmsMessageContract) {
            return $notification->toSmsMessage($notifiable);
        }
        if ($notification instanceof EasySmsChannelToSmsContract) {
            return $notification->toSms($notifiable);
        }
        if ($notification instanceof EasySmsChannelToSmsArrayContract) {
            return $notification->toSmsArray($notifiable);
        }

        throw new RuntimeException('Notifications do not implement the toSmsArray,toSms,toSmsMessage contract.');
    }
}
