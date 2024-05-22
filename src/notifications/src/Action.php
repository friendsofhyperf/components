<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Notifications;

class Action
{
    /**
     * Create a new action instance.
     * @param string $text the text of the action
     * @param string $url the URL of the action
     */
    public function __construct(
        public string $text,
        public string $url,
    ) {
    }
}
