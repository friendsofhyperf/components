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
        /* @phpstan-ignore-next-line */
        return fn ($format) => collect(sscanf($this->value, $format));
    }
}
