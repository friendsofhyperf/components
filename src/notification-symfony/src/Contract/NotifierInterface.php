<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Notification\Symfony\Contract;

class_alias(\Symfony\Component\Notifier\NotifierInterface::class, NotifierInterface::class);

if (!interface_exists(NotifierInterface::class)) {
    interface NotifierInterface extends \Symfony\Component\Notifier\NotifierInterface
    {
    }
}