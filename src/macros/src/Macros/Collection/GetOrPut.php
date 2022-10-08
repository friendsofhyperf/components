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
class GetOrPut
{
    public function __invoke()
    {
        return function ($key, $value) {
            /* @phpstan-ignore-next-line */
            if (array_key_exists($key, $this->items)) {
                /* @phpstan-ignore-next-line */
                return $this->items[$key];
            }

            /* @phpstan-ignore-next-line */
            $this->offsetSet($key, $value = value($value));

            return $value;
        };
    }
}
