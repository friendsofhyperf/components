<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.x/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\Macros\Macros\Str;

use Hyperf\Utils\Str;

class Lcfirst
{
    public function __invoke()
    {
        return fn ($string) => Str::lower(Str::substr($string, 0, 1)) . Str::substr($string, 1);
    }
}
