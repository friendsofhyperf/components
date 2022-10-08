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
class SchemeAndHttpHost
{
    public function __invoke()
    {
        return function () {
            $https = $this->getServerParams('HTTPS')[0] ?? null;
            /* @phpstan-ignore-next-line */
            return ($https ? 'https' : 'http') . '://' . $this->httpHost();
        };
    }
}
