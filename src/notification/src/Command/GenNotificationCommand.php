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

use Hyperf\Command\Annotation\Command;
use Hyperf\Command\Command as Base;

#[Command(name: 'gen:notification', description: 'Generate a new notification class', signature: 'gen:notification {name : The name of the notification class} {--force : Overwrite the notification if it exists}')]
class GenNotificationCommand extends Base
{
}
