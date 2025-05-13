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

use ArrayAccess;
use InvalidArgumentException;

class Arr
{
    /**
     * Determine whether the given value is arrayable.
     *
     * @param mixed $value
     * @return bool
     */
    public static function arrayable($value)
    {
    }

    /**
     * Get an array item from an array using "dot" notation.
     * @return array
     * @throws InvalidArgumentException
     */
    public static function array(ArrayAccess|array $array, string|int|null $key, ?array $default = null)
    {
    }

    /**
     * Get a boolean item from an array using "dot" notation.
     * @return bool
     * @throws InvalidArgumentException
     */
    public static function boolean(ArrayAccess|array $array, string|int|null $key, ?bool $default = null)
    {
    }

    /**
     * Get a float item from an array using "dot" notation.
     * @return float
     * @throws InvalidArgumentException
     */
    public static function float(ArrayAccess|array $array, string|int|null $key, ?float $default = null)
    {
    }

    /**
     * Get the underlying array of items from the given argument.
     *
     * @template TKey of array-key = array-key
     * @template TValue = mixed
     *
     * @param array<TKey, TValue>|Enumerable<TKey, TValue>|Arrayable<TKey, TValue>|WeakMap<object, TValue>|Traversable<TKey, TValue>|Jsonable|JsonSerializable|object $items
     * @return ($items is WeakMap ? list<TValue> : array<TKey, TValue>)
     *
     * @throws InvalidArgumentException
     */
    public static function from($items)
    {
    }

    /**
     * Get an integer item from an array using "dot" notation.
     * @return int
     * @throws InvalidArgumentException
     */
    public static function integer(ArrayAccess|array $array, string|int|null $key, ?int $default = null)
    {
    }

    /**
     * Get a string item from an array using "dot" notation.
     * @return string
     * @throws InvalidArgumentException
     */
    public static function string(ArrayAccess|array $array, string|int|null $key, ?string $default = null)
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
}
