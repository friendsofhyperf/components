<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Notification;

/**
 * @method bool shouldSend(mixed $notifiable, string $notification)
 * @method array|object toDatabase(mixed $notifiable)
 * @method string databaseType(mixed $notifiable)
 */
class Notification
{
    /**
     * The unique identifier for the notification.
     */
    public ?string $id = null;

    /**
     * The locale to be used when sending the notification.
     */
    public ?string $locale = null;

    /**
     * Set the locale to send this notification in.
     */
    public function locale(string $locale): static
    {
        $this->locale = $locale;

        return $this;
    }
}
