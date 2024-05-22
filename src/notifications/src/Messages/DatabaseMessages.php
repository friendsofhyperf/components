<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Notifications\Messages;

class DatabaseMessages
{
    /**
     * Create a new message instance.
     * @param array $data the data that should be stored with the notification
     */
    public function __construct(
        public array $data
    ) {
    }
}
