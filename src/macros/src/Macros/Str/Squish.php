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

class Squish
{
    public function __invoke()
    {
        return static function ($value) {
            return preg_replace('~(\s|\x{3164})+~u', ' ', preg_replace('~^[\s﻿]+|[\s﻿]+$~u', '', $value));
        };
    }
}
