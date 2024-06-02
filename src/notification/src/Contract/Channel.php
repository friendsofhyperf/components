<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Notification\Contract;

use FriendsOfHyperf\Notification\Notification;

interface Channel
{
    public function send(mixed $notifiable, Notification $notification, ?string $transportName = null): mixed;
}
