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
 * @mixin \Hyperf\Utils\Stringable
 */
class WhenNotExactly
{
    public function __invoke()
    {
        return function ($needles, $callback, $default = null) {
            return $this->when(! $this->exactly($needles), $callback, $default);
        };
    }
}
