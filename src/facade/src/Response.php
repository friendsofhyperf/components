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

use Hyperf\HttpServer\Contract\ResponseInterface as Accessor;

/**
 * @mixin Accessor
 * @mixin \Psr\Http\Message\ResponseInterface
 */
class Response extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Accessor::class;
    }
}
