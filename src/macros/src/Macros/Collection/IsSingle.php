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
class IsSingle
{
    public function __invoke()
    {
        return function () {
            if ($this->count() === 1) {
                return true;
            }

            return false;
        };
    }
}
