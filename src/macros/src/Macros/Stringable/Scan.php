<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.x/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\Macros\Macros\Stringable;

/**
 * @mixin \Illuminate\Support\Stringable
 */
class Scan
{
    public function __invoke()
    {
        return function ($format) {
            /* @phpstan-ignore-next-line */
            return collect(sscanf($this->value, $format));
        };
    }
}
