<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Facade;

use Hyperf\Event\EventDispatcher;
use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * @mixin EventDispatcher
 */
class Event extends Facade
{
    protected static function getFacadeAccessor()
    {
        return EventDispatcherInterface::class;
    }
}
