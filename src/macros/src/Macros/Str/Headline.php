<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.x/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\Macros\Macros\Str;

use Hyperf\Utils\Str;

class Headline
{
    public function __invoke()
    {
        return function ($value) {
            $parts = explode(' ', $value);

            $parts = count($parts) > 1
                ? $parts = array_map([Str::class, 'title'], $parts)
                : $parts = array_map([Str::class, 'title'], Str::ucsplit(implode('_', $parts)));

            $collapsed = Str::replace(['-', '_', ' '], '_', implode('_', $parts));

            return implode(' ', array_filter(explode('_', $collapsed)));
        };
    }
}
