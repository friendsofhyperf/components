<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.x/README.md
 * @contact  huangdijia@gmail.com
 */
use ArgumentCountError;
use Hyperf\Utils\Arr;
use Hyperf\Utils\Collection;

if (! Arr::hasMacro('isList')) {
    Arr::macro('isList', function ($array) {
        return ! Arr::isAssoc($array);
    });
}

if (! Arr::hasMacro('keyBy')) {
    Arr::macro('keyBy', function ($array, $keyBy) {
        return Collection::make($array)->keyBy($keyBy)->all();
    });
}

if (! Arr::hasMacro('join')) {
    Arr::macro('join', function ($array, $glue, $finalGlue = '') {
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
    });
}

if (! Arr::hasMacro('map')) {
    Arr::macro('map', function (array $array, callable $callback) {
        $keys = array_keys($array);

        try {
            $items = array_map($callback, $array, $keys);
        } catch (ArgumentCountError) {
            $items = array_map($callback, $array);
        }

        return array_combine($keys, $items);
    });
}

if (! Arr::hasMacro('prependKeysWith')) {
    Arr::macro('prependKeysWith', function ($array, $prependWith) {
        return Collection::make($array)->mapWithKeys(function ($item, $key) use ($prependWith) {
            return [$prependWith . $key => $item];
        })->all();
    });
}

if (! Arr::hasMacro('sortByMany')) {
    Arr::macro('sortByMany', function ($array, $comparisons = []) {
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
    });
}

if (! Arr::hasMacro('undot')) {
    Arr::macro('undot', function ($array) {
        $results = [];

        foreach ($array as $key => $value) {
            Arr::set($results, $key, $value);
        }

        return $results;
    });
}
