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

use FriendsOfHyperf\Macros\Exception\ItemNotFoundException;
use FriendsOfHyperf\Macros\Exception\MultipleItemsFoundException;
use Hyperf\Collection\Arr;
use Hyperf\Collection\Collection;
use stdClass;
use UnexpectedValueException;

use function Hyperf\Collection\data_get;
use function Hyperf\Collection\value;

/**
 * @property array $items
 * @mixin Collection
 */
class CollectionMixin
{
    public function doesntContain()
    {
        return fn ($key, $operator = null, $value = null) => ! $this->contains(...func_get_args());
    }

    public function ensure()
    {
        return fn ($type) => $this->each(function ($item) use ($type) {
            $itemType = get_debug_type($item);

            if ($itemType !== $type && ! $item instanceof $type) {
                throw new UnexpectedValueException(
                    sprintf("Collection should only include '%s' items, but '%s' found.", $type, $itemType)
                );
            }
        });
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

    public function intersectUsing()
    {
        /* @phpstan-ignore-next-line */
        return fn ($items, callable $callback) => new static(array_uintersect($this->items, $this->getArrayableItems($items), $callback));
    }

    public function intersectAssoc()
    {
        /* @phpstan-ignore-next-line */
        return fn ($items) => new static(array_intersect_assoc($this->items, $this->getArrayableItems($items)));
    }

    public function intersectAssocUsing()
    {
        /* @phpstan-ignore-next-line */
        return fn ($items, callable $callback) => new static(array_intersect_uassoc($this->items, $this->getArrayableItems($items), $callback));
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

    public function whenEmpty()
    {
        return fn (callable $callback, callable $default = null) => $this->when($this->isEmpty(), $callback, $default);
    }

    public function whenNotEmpty()
    {
        return fn (callable $callback, callable $default = null) => $this->when($this->isNotEmpty(), $callback, $default);
    }

    public function unlessEmpty()
    {
        return fn (callable $callback, callable $default = null) => $this->whenNotEmpty($callback, $default);
    }

    public function unlessNotEmpty()
    {
        return fn (callable $callback, callable $default = null) => $this->whenEmpty($callback, $default);
    }
}
