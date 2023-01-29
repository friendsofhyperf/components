<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.x/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\Macros;

use FriendsOfHyperf\Macros\Exceptions\ItemNotFoundException;
use FriendsOfHyperf\Macros\Exceptions\MultipleItemsFoundException;
use Hyperf\Utils\Arr;
use Hyperf\Utils\Collection;
use stdClass;

/**
 * @mixin Collection
 */
class CollectionMacros
{
    public function doesntContain()
    {
        return fn ($key, $operator = null, $value = null) => ! $this->contains(...func_get_args());
    }

    public function firstOrFail()
    {
        return function ($key = null, $operator = null, $value = null) {
            $args = func_get_args();
            $placeholder = new stdClass();
            /** @phpstan-ignore-next-line */
            $item = $this->when(func_num_args() > 0, fn ($collection) => $collection->where(...$args))->first(null, $placeholder);

            if ($item === $placeholder) {
                throw new ItemNotFoundException();
            }

            return $item;
        };
    }

    public function getOrPut()
    {
        return function ($key, $value) {
            /* @phpstan-ignore-next-line */
            if (array_key_exists($key, $this->items)) {
                /* @phpstan-ignore-next-line */
                return $this->items[$key];
            }

            /* @phpstan-ignore-next-line */
            $this->offsetSet($key, $value = value($value));

            return $value;
        };
    }

    public function hasAny()
    {
        return function ($key) {
            if ($this->isEmpty()) {
                return false;
            }

            $keys = is_array($key) ? $key : func_get_args();

            foreach ($keys as $value) {
                if ($this->has($value)) {
                    return true;
                }
            }

            return false;
        };
    }

    public function isSingle()
    {
        return fn () => $this->count() === 1;
    }

    public function pipeThrough()
    {
        return fn ($pipes) => static::make($pipes)->reduce(fn ($carry, $pipe) => $pipe($carry), $this);
    }

    public function skip()
    {
        return fn ($count) => $this->slice($count);
    }

    public function sliding()
    {
        return function ($size = 2, $step = 1) {
            $chunks = (int) floor(($this->count() - $size) / $step) + 1;

            return static::times($chunks, fn ($number) => $this->slice(($number - 1) * $step, $size));
        };
    }

    public function sole()
    {
        return function ($key = null, $operator = null, $value = null) {
            $args = func_get_args();
            $items = $this->when(func_num_args() > 0, function ($collection) use ($args) {
                return $collection->where(...$args);
            });

            if ($items->isEmpty()) {
                throw new ItemNotFoundException();
            }

            if ($items->count() > 1) {
                throw new MultipleItemsFoundException();
            }

            return $items->first();
        };
    }

    public function sortKeysUsing()
    {
        return function (callable $callback) {
            /** @phpstan-ignore-next-line */
            $items = $this->items;

            uksort($items, $callback);

            return new static($items);
        };
    }

    public function undot()
    {
        return fn () => new Collection(Arr::undot($this->all()));
    }

    public function value()
    {
        return function ($key, $default = null) {
            if ($value = $this->firstWhere($key, '=', true)) {
                return data_get($value, $key, $default);
            }

            return value($default);
        };
    }
}
