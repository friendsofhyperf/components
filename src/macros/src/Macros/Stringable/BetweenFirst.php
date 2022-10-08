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

use Hyperf\Utils\Str;

/**
 * @mixin \Illuminate\Support\Stringable
 */
class BetweenFirst
{
    public function __invoke()
    {
        /* @phpstan-ignore-next-line */
        return fn ($from, $to) => new static(Str::betweenFirst($this->value, $from, $to));
    }
}
