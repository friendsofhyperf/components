<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Macros;

/**
 * @mixin \Hyperf\Context\Context
 */
class ContextMixin
{
    public static function increment()
    {
        return fn (string $id, int $step = 1, ?int $coroutineId = null) => static::override(
            $id,
            fn ($value) => (int) $value + $step,
            $coroutineId
        );
    }

    public static function decrement()
    {
        return fn (string $id, int $step = 1, ?int $coroutineId = null) => static::override(
            $id,
            fn ($value) => (int) $value - $step,
            $coroutineId
        );
    }
}
