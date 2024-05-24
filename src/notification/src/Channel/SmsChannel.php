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
use Hyperf\Contract\ContainerInterface;
use Overtrue\EasySms\EasySms;
use Overtrue\EasySms\Message;
use RuntimeException;

use function Hyperf\Support\value;

class SmsChannel implements Channel
{
    private array $config;

    public function __construct(
        ConfigInterface $config,
        private ContainerInterface $container
    ) {
        $this->config = $config->get('notification.channels.sms', []);
    }

    public function send(mixed $notifiable, Notification $notification): mixed
    {
        return value(
            function ($phone, $params) {
                return $this->getClient()->send($phone, $params);
            },
            $notifiable->routeNotificationFor('sms', $notification),
            $this->buildPayload($notifiable, $notification)
        );
    }

    public function getClient(): EasySms
    {
        if ($this->container->has(EasySms::class)) {
            return $this->container->get(EasySms::class);
        }
        $sms = new EasySms($this->config);
        $this->container->set(EasySms::class, $sms);
        return $this->getClient();
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
