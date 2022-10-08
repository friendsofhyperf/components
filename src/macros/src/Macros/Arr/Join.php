<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.x/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\Macros\Macros\Arr;

class Join
{
    public function __invoke()
    {
        return function ($array, $glue, $finalGlue = '') {
            if ($finalGlue === '') {
                return implode($glue, $array);
            }

            if (count($array) === 0) {
                return '';
            }

            if (count($array) === 1) {
                return end($array);
            }

            $finalItem = array_pop($array);

            return implode($glue, $array) . $finalGlue . $finalItem;
        };
    }
}
