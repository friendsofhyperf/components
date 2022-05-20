<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/2.x/README.md
 * @contact  huangdijia@gmail.com
 */
use FriendsOfHyperf\Macros\Exceptions\ItemNotFoundException;
use FriendsOfHyperf\Macros\Exceptions\MultipleItemsFoundException;
use Hyperf\Utils\Arr;
use Hyperf\Utils\Collection;

if (! Collection::hasMacro('doesntContain')) {
    Collection::macro('doesntContain', function ($key, $operator = null, $value = null) {
        /* @var Collection $this */
        return ! $this->contains(...func_get_args());
    });
}

if (! Collection::hasMacro('firstOrFail')) {
    Collection::macro('firstOrFail', function ($key = null, $operator = null, $value = null) {
        $args = func_get_args();
        /** @var Collection $this */
        $placeholder = new stdClass();
        $item = $this->when(func_num_args() > 0, function ($collection) use ($args) {
            return $collection->where(...$args);
        })->first(null, $placeholder);

        if ($item === $placeholder) {
            throw new ItemNotFoundException();
        }

        return $item;
    });
}

if (! Collection::hasMacro('getOrPut')) {
    Collection::macro('getOrPut', function ($key, $value) {
        /** @var Collection $this */
        if (array_key_exists($key, $this->items)) {
            return $this->items[$key];
        }

        $this->offsetSet($key, $value = value($value));

        return $value;
    });
}

if (! Collection::hasMacro('hasAny')) {
    Collection::macro('hasAny', function ($key) {
        /** @var Collection $this */
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
    });
}

if (! Collection::hasMacro('isSingle')) {
    Collection::macro('isSingle', function () {
        /** @var Collection $this */
        if ($this->count() === 1) {
            return true;
        }

        return false;
    });
}

if (! Collection::hasMacro('pipeThrough')) {
    Collection::macro('pipeThrough', function ($pipes) {
        /* @var Collection $this */
        return static::make($pipes)->reduce(
            function ($carry, $pipe) {
                return $pipe($carry);
            },
            $this,
        );
    });
}

if (! Collection::hasMacro('sliding')) {
    Collection::macro('sliding', function ($size = 2, $step = 1) {
        /** @var Collection $this */
        $chunks = (int) floor(($this->count() - $size) / $step) + 1;

        return static::times($chunks, function ($number) use ($size, $step) {
            /** @var Collection $items */
            $items = $this;
            return $items->slice(($number - 1) * $step, $size);
        });
    });
}

if (! Collection::hasMacro('skip')) {
    Collection::macro('skip', function ($count) {
        /* @var Collection $this */
        return $this->slice($count);
    });
}

if (! Collection::hasMacro('sole')) {
    Collection::macro('sole', function ($key = null, $operator = null, $value = null) {
        $args = func_get_args();
        /** @var Collection $this */
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
    });
}

if (! Collection::hasMacro('sortKeysUsing')) {
    Collection::macro('sortKeysUsing', function (callable $callback) {
        /** @var Collection $this */
        $items = $this->items;

        uksort($items, $callback);

        return new static($items);
    });
}

if (! Collection::hasMacro('undot')) {
    Collection::macro('undot', function () {
        return new Collection(Arr::undot($this->all()));
    });
}

if (! Collection::hasMacro('value')) {
    Collection::macro('value', function ($key, $default = null) {
        if ($value = $this->firstWhere($key, '=', true)) {
            return data_get($value, $key, $default);
        }

        return value($default);
    });
}
