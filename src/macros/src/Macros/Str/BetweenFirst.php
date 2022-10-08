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

class BetweenFirst
{
    public function __invoke()
    {
        return function ($subject, $from, $to) {
            if ($from === '' || $to === '') {
                return $subject;
            }

            return Str::before(Str::after($subject, $from), $to);
        };
    }
}
