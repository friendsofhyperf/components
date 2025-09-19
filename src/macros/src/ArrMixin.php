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

use ArrayAccess;
use Hyperf\Collection\Arr;
use Hyperf\Collection\Enumerable;
use Hyperf\Contract\Arrayable;
use Hyperf\Contract\Jsonable;
use InvalidArgumentException;
use JsonSerializable;
use Traversable;
use WeakMap;

/**
 * @mixin Arr
 */
class ArrMixin
{
    public function arrayable()
    {
        return fn ($value) => is_array($value)
            || $value instanceof Arrayable
            || $value instanceof Traversable
            || $value instanceof Jsonable
            || $value instanceof JsonSerializable;
    }

    public function array()
    {
        return function (ArrayAccess|array $array, null|string|int $key, ?array $default = null) {
            $value = Arr::get($array, $key, $default);

            if (! is_array($value)) {
                throw new InvalidArgumentException(
                    sprintf('Array value for key [%s] must be an array, %s found.', $key, gettype($value))
                );
            }

            return $value;
        };
    }

    /**
     * Get a boolean item from an array using "dot" notation.
     */
    public function boolean()
    {
        return function (ArrayAccess|array $array, null|string|int $key, ?bool $default = null) {
            $value = Arr::get($array, $key, $default);

            if (! is_bool($value)) {
                throw new InvalidArgumentException(
                    sprintf('Array value for key [%s] must be a boolean, %s found.', $key, gettype($value))
                );
            }

            return $value;
        };
    }

    public static function every()
    {
        return fn ($array, callable $callback) => array_all($array, $callback);
    }

    /**
     * Get a float item from an array using "dot" notation.
     */
    public function float()
    {
        return function (ArrayAccess|array $array, null|string|int $key, ?float $default = null) {
            $value = Arr::get($array, $key, $default);

            if (! is_float($value)) {
                throw new InvalidArgumentException(
                    sprintf('Array value for key [%s] must be a float, %s found.', $key, gettype($value))
                );
            }

            return $value;
        };
    }

    public function from()
    {
        return fn ($items) => match (true) {
            is_array($items) => $items,
            $items instanceof Enumerable => $items->all(),
            $items instanceof Arrayable => $items->toArray(),
            $items instanceof WeakMap => iterator_to_array($items, false),
            $items instanceof Traversable => iterator_to_array($items),
            $items instanceof Jsonable => json_decode($items->__toString(), true),
            $items instanceof JsonSerializable => (array) $items->jsonSerialize(),
            is_object($items) => (array) $items,
            default => throw new InvalidArgumentException('Items cannot be represented by a scalar value.'),
        };
    }

    public static function hasAll()
    {
        return function ($array, $keys) {
            $keys = (array) $keys;

            if (! $array || $keys === []) {
                return false;
            }

            foreach ($keys as $key) {
                if (! static::has($array, $key)) {
                    return false;
                }
            }

            return true;
        };
    }

    /**
     * Get an integer item from an array using "dot" notation.
     */
    public function integer()
    {
        return function (ArrayAccess|array $array, null|string|int $key, ?int $default = null) {
            $value = Arr::get($array, $key, $default);

            if (! is_integer($value)) {
                throw new InvalidArgumentException(
                    sprintf('Array value for key [%s] must be an integer, %s found.', $key, gettype($value))
                );
            }

            return $value;
        };
    }

    /**
     * Get a string item from an array using "dot" notation.
     */
    public function string()
    {
        return function (ArrayAccess|array $array, null|string|int $key, ?string $default = null) {
            $value = Arr::get($array, $key, $default);

            if (! is_string($value)) {
                throw new InvalidArgumentException(
                    sprintf('Array value for key [%s] must be a string, %s found.', $key, gettype($value))
                );
            }

            return $value;
        };
    }

    public static function some()
    {
        return fn ($array, callable $callback) => array_any($array, $callback);
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
}
