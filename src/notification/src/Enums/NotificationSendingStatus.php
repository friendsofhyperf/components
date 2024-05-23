<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Notification\Enums;

/**
 * This enumeration is used to mark the message as ready to be sent.
 */
enum NotificationSendingStatus: int
{
    /**
     * The message is enabled.
     */
    case ENABLED = 1;

    /**
     * The message is blocked.
     */
    case BLOCKED = 0;
}
