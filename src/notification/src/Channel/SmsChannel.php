<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Notification\Channel;

use FriendsOfHyperf\Notification\Contract\Channel;
use FriendsOfHyperf\Notification\Notification;
use Hyperf\Contract\ConfigInterface;
use Overtrue\EasySms\EasySms;
use Overtrue\EasySms\Message;
use RuntimeException;

use function Hyperf\Support\value;

class SmsChannel implements Channel
{
    protected EasySms $client;

    public function __construct(ConfigInterface $config)
    {
        $this->client = new EasySms($config->get('notification.channels.sms', []));
    }

    public function send(mixed $notifiable, Notification $notification): mixed
    {
        return value(
            function ($phone, $params) {
                return $this->client->send($phone, $params);
            },
            $notifiable->routeNotificationFor('sms', $notification),
            $this->buildPayload($notifiable, $notification)
        );
    }

    public function getClient(): EasySms
    {
        return $this->client;
    }

    protected function buildPayload(mixed $notifiable, Notification $notification): array|Message
    {
        if (method_exists($notification, 'toSmsMessage')) {
            return $notification->toSmsMessage($notifiable);
        }
        if (method_exists($notification, 'toSms')) {
            return new Message($notification->toSms($notifiable));
        }
        if (method_exists($notification, 'toArray')) {
            return $notification->toArray($notifiable);
        }
        throw new RuntimeException('Notification is missing toSmsMessage / toSms / toArray method.');
    }
}
