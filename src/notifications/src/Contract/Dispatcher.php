<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Notifications\Contract;

use FriendsOfHyperf\Notifications\Notification;

interface Dispatcher
{
    public function send(mixed $notifiables, Notification $notification): void;
}
