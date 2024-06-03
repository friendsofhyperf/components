<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Mailer\Enums;

enum MessageSendingStatusEnum: int
{
    /**
     * The message was sent successfully.
     */
    case SUCCESS = 1;

    /**
     * The message failed to send.
     */
    case FAILED = 0;
}
