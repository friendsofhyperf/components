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
use Overtrue\EasySms\EasySms;
use Overtrue\EasySms\Message;
use Psr\Container\ContainerInterface;
use RuntimeException;

class EasySmsChannel implements Channel
{
    protected ?EasySms $client = null;

    public function __construct(protected ContainerInterface $container)
    {
    }

    public function send(mixed $notifiable, Notification $notification): mixed
    {
        return $this->client->send(
            $notifiable->routeNotificationFor('sms', $notification),
            $this->buildPayload($notifiable, $notification)
        );
    }

    public function getClient(): EasySms
    {
        if (! $this->client) {
            if (! $this->container->has(EasySms::class)) {
                throw new RuntimeException('Please bind `Overtrue\EasySms\EasySms` to container first.');
            }

            $this->client = $this->container->get(EasySms::class);
        }

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
