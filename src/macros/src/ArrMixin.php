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
use InvalidArgumentException;

/**
 * @mixin Arr
 */
class ArrMixin
{
    public static function array()
    {
        return function (ArrayAccess|array $array, string|int|null $key, ?array $default = null) {
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
    public static function boolean()
    {
        return function (ArrayAccess|array $array, string|int|null $key, ?bool $default = null) {
            $value = Arr::get($array, $key, $default);

            if (! is_bool($value)) {
                throw new InvalidArgumentException(
                    sprintf('Array value for key [%s] must be a boolean, %s found.', $key, gettype($value))
                );
            }

            return $value;
        };
    }

    /**
     * Get a float item from an array using "dot" notation.
     */
    public static function float()
    {
        return function (ArrayAccess|array $array, string|int|null $key, ?float $default = null) {
            $value = Arr::get($array, $key, $default);

            if (! is_float($value)) {
                throw new InvalidArgumentException(
                    sprintf('Array value for key [%s] must be a float, %s found.', $key, gettype($value))
                );
            }

            return $value;
        };
    }

    /**
     * Get an integer item from an array using "dot" notation.
     */
    public static function integer()
    {
        return function (ArrayAccess|array $array, string|int|null $key, ?int $default = null) {
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
    public static function string()
    {
        return function (ArrayAccess|array $array, string|int|null $key, ?string $default = null) {
            $value = Arr::get($array, $key, $default);

            if (! is_string($value)) {
                throw new InvalidArgumentException(
                    sprintf('Array value for key [%s] must be a string, %s found.', $key, gettype($value))
                );
            }

            return $value;
        };
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
