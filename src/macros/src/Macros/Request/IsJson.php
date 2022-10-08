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

use Hyperf\Utils\Str;

/**
 * @mixin \Hyperf\HttpServer\Request
 */
class IsJson
{
    public function __invoke()
    {
        return fn () => Str::contains($this->header('CONTENT_TYPE') ?? '', ['/json', '+json']);
    }
}
