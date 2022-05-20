<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/macros.
 *
 * @link     https://github.com/friendsofhyperf/macros
 * @document https://github.com/friendsofhyperf/macros/blob/1.x/README.md
 * @contact  huangdijia@gmail.com
 */
namespace Hyperf\Utils;

class Arr
{
    /**
     * Determines if an array is a list.
     *
     * An array is a "list" if all array keys are sequential integers starting from 0 with no gaps in between.
     *
     * @param array $array
     * @return bool
     */
    public static function isList($array)
    {
    }

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
     * Run a map over each of the items in the array.
     *
     * @return array
     */
    public static function map(array $array, callable $callback)
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
