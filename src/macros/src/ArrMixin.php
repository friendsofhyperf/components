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
use Hyperf\Collection\Collection;

/**
 * @mixin Arr
 */
class ArrMixin
{
    public function join()
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

    public function keyBy()
    {
        return fn ($array, $keyBy) => Collection::make($array)->keyBy($keyBy)->all();
    }

    public function map()
    {
        return function (array $array, callable $callback) {
            $keys = array_keys($array);
            $items = array_map($callback, $array, $keys);

            return array_combine($keys, $items);
        };
    }

    public function prependKeysWith()
    {
        return fn ($array, $prependWith) => Collection::make($array)->mapWithKeys(fn ($item, $key) => [$prependWith . $key => $item])->all();
    }

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

    public static function sortDesc()
    {
        return fn ($array, $callback = null) => Collection::make($array)->sortByDesc($callback)->all();
    }

    /**
     * Recursively sort an array by keys and values in descending order.
     *
     * @return array
     */
    public function sortRecursiveDesc()
    {
        return fn ($array, $options = SORT_REGULAR) => $this->sortRecursive($array, $options, true);
    }

    public function undot()
    {
        return function ($array) {
            $results = [];

            foreach ($array as $key => $value) {
                Arr::set($results, $key, $value);
            }

            return $results;
        };
    }
}
