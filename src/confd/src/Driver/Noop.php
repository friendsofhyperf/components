<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Confd\Driver;

class Noop implements DriverInterface
{
    public function fetch(): array
    {
        return [];
    }

    public function loop(callable $callback): void
    {
    }
}
