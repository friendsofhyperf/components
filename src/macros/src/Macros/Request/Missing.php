<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.x/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\Macros\Macros\Request;

/**
 * @mixin \Hyperf\HttpServer\Request
 */
class Missing
{
    public function __invoke()
    {
        return function ($key) {
            $keys = is_array($key) ? $key : func_get_args();

            return ! $this->has($keys);
        };
    }
}
