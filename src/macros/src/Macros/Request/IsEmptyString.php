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
class IsEmptyString
{
    public function __invoke()
    {
        return function ($key) {
            $value = $this->input($key);

            return ! is_bool($value) && ! is_array($value) && trim((string) $value) === '';
        };
    }
}
