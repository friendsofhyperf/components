<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace Hyperf\Collection;

class Arr
{
    /**
     * Key an associative array by a field or using a callback.
     *
     * @param array $array
     * @param  array|callable|string
     * @param mixed $keyBy
     * @return array
     */
    public static function keyBy($array, $keyBy)
    {
    }

    /**
     * Join all items using a string. The final items can use a separate glue string.
     *
     * @param array $array
     * @param string $glue
     * @param string $finalGlue
     * @return string
     */
    public static function join($array, $glue, $finalGlue = '')
    {
    }

    /**
     * Run a map over each of the items in the array.
     *
     * @return array
     */
    public static function map(array $array, callable $callback)
    {
    }

    /**
     * Prepend the key names of an associative array.
     *
     * @param array $array
     * @param string $prependWith
     * @return array
     */
    public static function prependKeysWith($array, $prependWith)
    {
    }

    /**
     * Sort given array by many properties.
     *
     * @param array $array
     * @param array $comparisons
     * @return array
     */
    public static function sortByMany($array, $comparisons = [])
    {
    }

    /**
     * Sort the array in descending order using the given callback or "dot" notation.
     *
     * @param array $array
     * @param array|callable|string|null $callback
     * @return array
     */
    public static function sortDesc($array, $callback = null)
    {
    }

    /**
     * Recursively sort an array by keys and values in descending order.
     *
     * @param array $array
     * @param int $options
     * @return array
     */
    public function sortRecursiveDesc($array, $options = SORT_REGULAR)
    {
    }

    /**
     * Convert a flatten "dot" notation array into an expanded array.
     *
     * @param array|iterable $array
     * @return array
     */
    public static function undot($array)
    {
    }
}
