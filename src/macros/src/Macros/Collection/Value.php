<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.x/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\Macros\Macros\Collection;

/**
 * @mixin \Hyperf\Utils\Collection
 */
class Value
{
    public function __invoke()
    {
        return function ($key, $default = null) {
            if ($value = $this->firstWhere($key, '=', true)) {
                return data_get($value, $key, $default);
            }

            return value($default);
        };
    }
}
