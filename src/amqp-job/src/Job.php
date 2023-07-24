<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\AmqpJob;

use FriendsOfHyperf\AmqpJob\Concerns\Queueable;
use FriendsOfHyperf\AmqpJob\Contract\ShouldQueue;

abstract class Job implements ShouldQueue
{
    use Queueable;

    abstract public function handle(): void;
}
