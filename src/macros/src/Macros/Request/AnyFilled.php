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
class AnyFilled
{
    public function __invoke()
    {
        return function ($keys) {
            $keys = is_array($keys) ? $keys : func_get_args();

            foreach ($keys as $key) {
                /* @phpstan-ignore-next-line */
                if ($this->filled($key)) {
                    return true;
                }
            }

            return false;
        };
    }
}
