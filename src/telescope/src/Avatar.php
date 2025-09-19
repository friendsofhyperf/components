<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Telescope;

use Closure;
use Hyperf\Stringable\Str;

class Avatar
{
    /**
     * The callback that should be used to get the Telescope user avatar.
     *
     * @var null|Closure
     */
    protected static $callback;

    /**
     * Get an avatar URL for an entry user.
     *
     * @return null|string
     */
    public static function url(array $user)
    {
        if (empty($user['email'])) {
            return null;
        }

        if (isset(static::$callback)) {
            return static::resolve($user);
        }

        return 'https://www.gravatar.com/avatar/' . md5(Str::lower($user['email'])) . '?s=200';
    }

    /**
     * Register the Telescope user avatar callback.
     */
    public static function register(Closure $callback)
    {
        static::$callback = $callback;
    }

    /**
     * Find the custom avatar for a user.
     *
     * @param array $user
     * @return null|string
     */
    protected static function resolve($user)
    {
        if (static::$callback !== null) {
            return call_user_func(static::$callback, $user['id'], $user['email']);
        }

        return null;
    }
}
