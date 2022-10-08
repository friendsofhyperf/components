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
class PipeThrough
{
    public function __invoke()
    {
        return fn ($pipes) => static::make($pipes)->reduce(fn ($carry, $pipe) => $pipe($carry), $this);
    }
}
