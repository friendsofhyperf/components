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
class HttpHost
{
    public function __invoke()
    {
        return function () {
            if ($host = $this->getHeader('HOST')[0] ?? null) {
                return $host;
            }
            if ($host = $this->getServerParams('SERVER_NAME')[0] ?? null) {
                return $host;
            }
            if ($host = $this->getServerParams('SERVER_ADDR')[0] ?? null) {
                return $host;
            }
            return '';
        };
    }
}
