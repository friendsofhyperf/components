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
use Throwable;

class FailedHandle extends Event
{
    public function __construct(TaskMessage $message, protected ?Throwable $throwable)
    {
        parent::__construct($message);
    }

    public function getThrowable(): ?Throwable
    {
        return $this->throwable;
    }
}
