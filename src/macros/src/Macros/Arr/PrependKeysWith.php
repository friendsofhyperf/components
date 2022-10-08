<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.x/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\Macros\Macros\Arr;

use Hyperf\Utils\Collection;

class PrependKeysWith
{
    public function __invoke()
    {
        return fn ($array, $prependWith) => Collection::make($array)
            ->mapWithKeys(function ($item, $key) use ($prependWith) {
                return [$prependWith . $key => $item];
            })->all();
    }
}
