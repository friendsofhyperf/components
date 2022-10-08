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
class Sliding
{
    public function __invoke()
    {
        return function ($size = 2, $step = 1) {
            $chunks = (int) floor(($this->count() - $size) / $step) + 1;

            return static::times($chunks, function ($number) use ($size, $step) {
                /** @var Collection $items */
                $items = $this;
                return $items->slice(($number - 1) * $step, $size);
            });
        };
    }
}
