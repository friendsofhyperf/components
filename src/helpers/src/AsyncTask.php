<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\Helpers\AsyncTask;

use FriendsOfHyperf\AsyncTask\Task;
use FriendsOfHyperf\AsyncTask\TaskInterface;

function dispatch(TaskInterface $job, ...$arguments): bool
{
    Task::deliver($job, ...$arguments);
    return true;
}
