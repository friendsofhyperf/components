<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/1.x/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\Facade;

use Hyperf\HttpServer\Contract\RequestInterface as Accessor;

/**
 * @mixin Accessor
 */
class Request extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Accessor::class;
    }
}
