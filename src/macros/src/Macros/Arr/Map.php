<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.x/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\Macros\Macros\Arr;

class Map
{
    public function __invoke()
    {
        return function (array $array, callable $callback) {
            $keys = array_keys($array);
            $items = array_map($callback, $array, $keys);

            return array_combine($keys, $items);
        };
    }
}
