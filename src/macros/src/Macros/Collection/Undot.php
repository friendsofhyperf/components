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

use Hyperf\Utils\Arr;
use Hyperf\Utils\Collection;

/**
 * @mixin \Hyperf\Utils\Collection
 */
class Undot
{
    public function __invoke()
    {
        return fn () => new Collection(Arr::undot($this->all()));
    }
}
