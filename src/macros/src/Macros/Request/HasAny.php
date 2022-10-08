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

use Hyperf\Utils\Arr;

/**
 * @mixin \Hyperf\HttpServer\Request
 */
class HasAny
{
    public function __invoke()
    {
        return function ($keys) {
            $keys = is_array($keys) ? $keys : func_get_args();

            $input = $this->all();

            return Arr::hasAny($input, $keys);
        };
    }
}
