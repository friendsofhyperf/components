<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Macros;

use Hyperf\Collection\Arr;

/**
 * @mixin Arr
 */
class ArrMixin
{
    public function sortByMany()
    {
        return function ($array, $comparisons = []) {
            usort($array, function ($a, $b) use ($comparisons) {
                foreach ($comparisons as $cmp) {
                    // destruct comparison array to variables
                    // with order set by default to 1
                    [$prop, $ascending] = Arr::wrap($cmp) + [1 => true];
                    $result = 0;

                    if (is_callable($prop)) {
                        $result = $prop($a, $b);
                    } else {
                        $values = [Arr::get($a, $prop), Arr::get($b, $prop)];

                        if (! $ascending) {
                            $values = array_reverse($values);
                        }

                        $result = $values[0] <=> $values[1];
                    }

                    // if result is 0, values are equal
                    // so we have to order items by next comparison
                    if ($result === 0) {
                        continue;
                    }

                    return $result;
                }
            });

            return $array;
        };
    }
}
