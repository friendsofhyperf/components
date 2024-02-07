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

use Hyperf\HttpServer\Contract\ResponseInterface;
use Override;

/**
 * @mixin ResponseInterface
 * @mixin \Psr\Http\Message\ResponseInterface
 */
class Response extends Facade
{
    #[Override]
    protected static function getFacadeAccessor()
    {
        return ResponseInterface::class;
    }
}
