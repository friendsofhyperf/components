<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Notification\Command;

use Hyperf\Command\Command;

class GenNotificationCommand extends Command
{
    protected ?string $signature = 'gen:notification {name : The name of the notification class} {--force : Overwrite the notification if it exists}';

    protected string $description = 'Generate a new notification class';
}
