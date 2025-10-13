<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Sentry\Util;

use Hyperf\Context\Context;
use WeakMap;

/**
 * @template TKey of object
 * @template TValue of object
 */
class CoContainer
{
    public const CONTEXT_KEY = 'sentry.context.container';

    /**
     * @param TKey $key
     * @return null|TValue
     */
    public static function get(?object $key = null): ?object
    {
        return self::getContainer()[$key] ?? null;
    }

    /**
     * @param TKey $key
     * @param TValue $value
     * @return TValue
     */
    public static function set(object $key, object $value): object
    {
        self::getContainer()[$key] = $value;

        return $value;
    }

    private static function getContainer(): WeakMap
    {
        return Context::get(self::CONTEXT_KEY, fn () => new WeakMap());
    }
}
