<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Macros;

use Hyperf\Collection\Collection;

use function Hyperf\Collection\value;

/**
 * @property array $items
 * @mixin Collection
 */
class CollectionMixin
{
    public function getOrPut()
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

    public function isSingle()
    {
        return fn () => $this->count() === 1;
    }
}
