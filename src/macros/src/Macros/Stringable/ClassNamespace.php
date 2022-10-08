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
class ClassNamespace
{
    public function __invoke()
    {
        /* @phpstan-ignore-next-line */
        return fn () => new static(class_basename($this->value));
    }
}
