<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Notifications\Channels;

use FriendsOfHyperf\Notifications\Attributes\Channel;
use FriendsOfHyperf\Notifications\Contract\Channel as Contract;
use FriendsOfHyperf\Notifications\Notification;
use Hyperf\Database\Model\Model;
use RuntimeException;

#[Channel('database')]
class DatabaseChannel implements Contract
{
    /**
     * Send the given notification.
     */
    public function send(mixed $notifiable, Notification $notification): Model
    {
        return $notifiable->routeNotificationFor('database', $notification)->create(
            $this->buildPayload($notifiable, $notification)
        );
    }

    /**
     * Build an array payload for the DatabaseNotification Model.
     */
    protected function buildPayload(mixed $notifiable, Notification $notification): array
    {
        return [
            'id' => $notification->id,
            'type' => method_exists($notification, 'databaseType')
                ? $notification->databaseType($notifiable)
                : get_class($notification),
            'data' => $this->getData($notifiable, $notification),
            'read_at' => null,
        ];
    }

    /**
     * Get the data for the notification.
     */
    protected function getData(mixed $notifiable, Notification $notification): array
    {
        if (method_exists($notification, 'toDatabase')) {
            return is_array($data = $notification->toDatabase($notifiable))
                ? $data : $data->data;
        }

        if (method_exists($notification, 'toArray')) {
            return $notification->toArray($notifiable);
        }

        throw new RuntimeException('Notification is missing toDatabase / toArray method.');
    }
}
