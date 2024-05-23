<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Tests\Notification\Stubs;

use FriendsOfHyperf\Notification\Channel\DatabaseChannel;
use FriendsOfHyperf\Notification\Notification;

class ExtendedDatabaseChannel extends DatabaseChannel
{
    protected function buildPayload(mixed $notifiable, Notification $notification): array
    {
        return array_merge(parent::buildPayload($notifiable, $notification), [
            'something' => 'else',
        ]);
    }
}
