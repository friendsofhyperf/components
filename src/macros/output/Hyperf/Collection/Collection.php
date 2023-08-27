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

use Hyperf\Contract\Arrayable;
use UnexpectedValueException;

class Collection
{
    /**
     * Determine if an item is missing in the collection.
     * Determine if an item is not contained in the collection.
     *
     * @param string $key
     * @param mixed $operator
     * @param mixed $value
     * @return bool
     */
    public function doesntContain($key, $operator = null, $value = null)
    {
    }

    /**
     * Ensure that every item in the collection is of the expected type.
     *
     * @template TEnsureOfType
     *
     * @param class-string<TEnsureOfType> $type
     * @return static<mixed, TEnsureOfType>
     *
     * @throws UnexpectedValueException
     */
    public function ensure($type)
    {
    }

    /**
     * Get the first item in the collection but throw an exception if no matching items exist.
     *
     * @param string $key
     * @param mixed $operator
     * @param mixed $value
     * @return mixed
     * @throws ItemNotFoundException
     */
    public function firstOrFail($key = null, $operator = null, $value = null)
    {
    }

    /**
     * Get an item from the collection by key or add it to collection if it does not exist.
     *
     * @param mixed $key
     * @param mixed $value
     * @return mixed
     */
    public function getOrPut($key, $value)
    {
    }

    /**
     * Determine if any of the keys exist in the collection.
     *
     * @param mixed $key
     * @return bool
     */
    public function hasAny($key)
    {
    }

    /**
     * Determine if the collection contains a single element.
     *
     * @return bool
     */
    public function isSingle()
    {
    }

    /**
     * Intersect the collection with the given items, using the callback.
     *
     * @param Arrayable<array-key, TValue>|iterable<array-key, TValue> $items
     * @param callable(TValue, TValue): int $callback
     * @return static
     */
    public function intersectUsing($items, callable $callback)
    {
    }

    /**
     * Intersect the collection with the given items with additional index check.
     *
     * @param Arrayable<TKey, TValue>|iterable<TKey, TValue> $items
     * @return static
     */
    public function intersectAssoc($items)
    {
    }

    /**
     * Intersect the collection with the given items with additional index check, using the callback.
     *
     * @param Arrayable<array-key, TValue>|iterable<array-key, TValue> $items
     * @param callable(TValue, TValue): int $callback
     * @return static
     */
    public function intersectAssocUsing($items, callable $callback)
    {
    }

    /**
     * Pass the collection through a series of callable pipes and return the result.
     *
     * @param array<callable> $pipes
     * @return mixed
     */
    public function pipeThrough($pipes)
    {
    }

    /**
     * Create chunks representing a "sliding window" view of the items in the collection.
     *
     * @param int $size
     * @param int $step
     * @return static
     */
    public function sliding($size = 2, $step = 1)
    {
    }

    /**
     * Skip the first {$count} items.
     *
     * @param int $count
     * @return static
     */
    public function skip($count)
    {
    }

    /**
     * Get the first item in the collection, but only if exactly one item exists. Otherwise, throw an exception.
     *
     * @param string $key
     * @param mixed $operator
     * @param mixed $value
     * @return mixed
     * @throws ItemNotFoundException
     * @throws MultipleItemsFoundException
     */
    public function sole($key = null, $operator = null, $value = null)
    {
    }

    /**
     * Sort the collection keys using a callback.
     *
     * @return static
     */
    public function sortKeysUsing(callable $callback)
    {
    }

    /**
     * Convert a flatten "dot" notation array into an expanded array.
     *
     * @return Collection
     */
    public function undot()
    {
    }

    /**
     * Get a single key's value from the first matching item in the collection.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function value($key, $default = null)
    {
    }

    /**
     * Apply the callback if the collection is empty.
     *
     * @template TWhenEmptyReturnType
     *
     * @param (callable($this): TWhenEmptyReturnType) $callback
     * @param (callable($this): TWhenEmptyReturnType)|null $default
     * @return $this|TWhenEmptyReturnType
     */
    public function whenEmpty(callable $callback, callable $default = null)
    {
    }

    /**
     * Apply the callback if the collection is not empty.
     *
     * @template TWhenNotEmptyReturnType
     *
     * @param callable($this): TWhenNotEmptyReturnType $callback
     * @param (callable($this): TWhenNotEmptyReturnType)|null $default
     * @return $this|TWhenNotEmptyReturnType
     */
    public function whenNotEmpty(callable $callback, callable $default = null)
    {
    }

    /**
     * Apply the callback unless the collection is empty.
     *
     * @template TUnlessEmptyReturnType
     *
     * @param callable($this): TUnlessEmptyReturnType $callback
     * @param (callable($this): TUnlessEmptyReturnType)|null $default
     * @return $this|TUnlessEmptyReturnType
     */
    public function unlessEmpty(callable $callback, callable $default = null)
    {
    }

    /**
     * Apply the callback unless the collection is not empty.
     *
     * @template TUnlessNotEmptyReturnType
     *
     * @param callable($this): TUnlessNotEmptyReturnType $callback
     * @param (callable($this): TUnlessNotEmptyReturnType)|null $default
     * @return $this|TUnlessNotEmptyReturnType
     */
    public function unlessNotEmpty(callable $callback, callable $default = null)
    {
    }
}
