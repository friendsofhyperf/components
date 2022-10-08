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
use stdClass;

/**
 * @mixin \Hyperf\Utils\Collection
 */
class FirstOrFail
{
    public function __invoke()
    {
        return function ($key = null, $operator = null, $value = null) {
            $args = func_get_args();
            $placeholder = new stdClass();
            /** @phpstan-ignore-next-line */
            $item = $this->when(func_num_args() > 0, function ($collection) use ($args) {
                return $collection->where(...$args);
            })->first(null, $placeholder);

            if ($item === $placeholder) {
                throw new ItemNotFoundException();
            }

            return $item;
        };
    }
}
