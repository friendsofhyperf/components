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

use Hyperf\Collection\Enumerable;
use Hyperf\Collection\LazyCollection;

/**
 * @property array $items
 * @mixin LazyCollection
 */
class LazyCollectionMixin
{
    public function isSingle()
    {
        return fn () => $this->count() === 1;
    }

    public function collapseWithKeys()
    {
        return function () {
            return new static(function () {
                foreach ($this as $values) { // @phpstan-ignore-line
                    if (is_array($values) || $values instanceof Enumerable) {
                        foreach ($values as $key => $value) {
                            yield $key => $value;
                        }
                    }
                }
            });
        };
    }
}
