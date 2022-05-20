<?php

declare(strict_types=1);
/**
 * This file is part of hyperf/facade.
 *
 * @link     https://github.com/friendsofhyperf/facade
 * @document https://github.com/friendsofhyperf/facade/blob/1.x/README.md
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
