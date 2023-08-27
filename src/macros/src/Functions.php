<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */
if (! function_exists('array_is_list')) {
    /**
     * Determine if the given value is a list of items.
     * @return bool return true if the array keys are 0 .. count($array)-1 in that order. For other arrays, it returns false. For non-arrays, it throws a TypeError.
     */
    function array_is_list(array $array): bool
    {
        if ($array === [] || $array === array_values($array)) {
            return true;
        }

        $nextKey = -1;

        foreach ($array as $k => $v) {
            if ($k !== ++$nextKey) {
                return false;
            }
        }

        return true;
    }
}
