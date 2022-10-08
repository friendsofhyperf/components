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

use voku\helper\ASCII;

class IsAscii
{
    public function __invoke()
    {
        return static function ($value) {
            return ASCII::is_ascii((string) $value);
        };
    }
}
