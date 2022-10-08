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
class Collect
{
    public function __invoke()
    {
        return function ($key = null) {
            if (is_null($key)) {
                return $this->all();
            }

            return collect(is_array($key) ? $this->only($key) : $this->input($key));
        };
    }
}
