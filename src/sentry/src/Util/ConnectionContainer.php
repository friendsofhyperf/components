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

use WeakMap;

/**
 * @template TKey of object
 * @template TValue
 */
class ConnectionContainer
{
    protected static ?WeakMap $mapping = null;

    /**
     * @param TKey $object
     * @param TValue $value
     */
    public static function set(object $object, mixed $value): void
    {
        if (self::$mapping === null) {
            self::$mapping = new WeakMap();
        }

        self::$mapping[$object] = $value;
    }

    /**
     * @param TKey $object
     * @return null|TValue
     */
    public static function get(object $object): mixed
    {
        if (self::$mapping === null) {
            return null;
        }

        return self::$mapping[$object] ?? null;
    }
}
