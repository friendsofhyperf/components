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

class FlushCache
{
    public function __invoke()
    {
        return function () {
            /* @phpstan-ignore-next-line */
            static::$snakeCache = [];
            /* @phpstan-ignore-next-line */
            static::$camelCache = [];
            /* @phpstan-ignore-next-line */
            static::$studlyCache = [];
        };
    }
}
