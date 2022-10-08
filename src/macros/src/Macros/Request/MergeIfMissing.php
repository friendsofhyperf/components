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
class MergeIfMissing
{
    public function __invoke()
    {
        return function (array $input) {
            return $this->merge(collect($input)->filter(fn ($value, $key) => $this->missing($key))->toArray());
        };
    }
}
