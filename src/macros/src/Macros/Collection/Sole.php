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

use FriendsOfHyperf\Macros\Exceptions\ItemNotFoundException;
use FriendsOfHyperf\Macros\Exceptions\MultipleItemsFoundException;

/**
 * @mixin \Hyperf\Utils\Collection
 */
class Sole
{
    public function __invoke()
    {
        return function ($key = null, $operator = null, $value = null) {
            $args = func_get_args();
            $items = $this->when(func_num_args() > 0, function ($collection) use ($args) {
                return $collection->where(...$args);
            });

            if ($items->isEmpty()) {
                throw new ItemNotFoundException();
            }

            if ($items->count() > 1) {
                throw new MultipleItemsFoundException();
            }

            return $items->first();
        };
    }
}
