<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Notifications\Attributes;

use Hyperf\Di\MetadataCollector;

class AttributesCollector extends MetadataCollector
{
    protected static array $container = [];

    public static function collect(string $name, string $class): void
    {
        static::$container[$name] = $class;
    }

    public static function get(string $key, $default = null)
    {
        return static::$container[$key] ?? $default;
    }
}
