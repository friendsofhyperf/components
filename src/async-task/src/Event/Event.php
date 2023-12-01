<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\AsyncTask\Event;

use FriendsOfHyperf\AsyncTask\TaskMessage;

class Event
{
    public function __construct(protected TaskMessage $message)
    {
    }

    public function getMessage(): TaskMessage
    {
        return $this->message;
    }
}
