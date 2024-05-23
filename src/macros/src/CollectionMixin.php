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
    public function isSingle()
    {
        return fn () => $this->count() === 1;
    }
}
