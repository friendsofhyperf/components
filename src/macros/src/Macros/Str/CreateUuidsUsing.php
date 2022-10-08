<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.x/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\Macros\Macros\Str;

use FriendsOfHyperf\Macros\Foundation\UuidContainer;

class CreateUuidsUsing
{
    public function __invoke()
    {
        return fn (callable $factory = null) => UuidContainer::$uuidFactory = $factory;
    }
}
