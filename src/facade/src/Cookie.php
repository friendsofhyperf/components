<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Facade;

use Hyperf\HttpMessage\Cookie\CookieJar;
use Hyperf\HttpMessage\Cookie\CookieJarInterface as Accessor;

/**
 * @mixin CookieJar
 */
class Cookie extends Facade
{
    /**
     * @param string $key
     * @return bool
     */
    public static function has($key)
    {
        return ! is_null(Request::cookie($key));
    }

    /**
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function get($key, $default = null)
    {
        return Request::cookie($key, $default);
    }

    protected static function getFacadeAccessor()
    {
        return Accessor::class;
    }
}
