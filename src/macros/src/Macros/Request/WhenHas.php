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
class WhenHas
{
    public function __invoke()
    {
        return function ($key, callable $callback, callable $default = null) {
            if ($this->has($key)) {
                return $callback(data_get($this->all(), $key)) ?: $this;
            }

            if ($default) {
                return $default();
            }

            return $this;
        };
    }
}
