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

class IsUlid
{
    public function __invoke()
    {
        return function ($value) {
            if (! is_string($value)) {
                return false;
            }

            if (\strlen($value) !== 26) {
                return false;
            }

            if (strspn($value, '0123456789ABCDEFGHJKMNPQRSTVWXYZabcdefghjkmnpqrstvwxyz') !== 26) {
                return false;
            }

            return $value[0] <= '7';
        };
    }
}
