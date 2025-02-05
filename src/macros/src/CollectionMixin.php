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

/**
 * @property array $items
 * @mixin Collection
 */
class CollectionMixin
{
    public function collapseWithKeys()
    {
        return function () {
            if (! $this->items) { // @phpstan-ignore-line
                return new static();
            }

            $results = [];

            foreach ($this->items as $key => $values) { // @phpstan-ignore-line
                if ($values instanceof Collection) {
                    $values = $values->all();
                } elseif (! is_array($values)) {
                    continue;
                }

                $results[$key] = $values;
            }

            return new static(array_replace(...$results));
        };
    }
}
