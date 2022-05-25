<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/2.x/README.md
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
     * Convert a flatten "dot" notation array into an expanded array.
     *
     * @param array|iterable $array
     * @return array
     */
    public static function undot($array)
    {
    }
}
