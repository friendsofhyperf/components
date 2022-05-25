<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.x/README.md
 * @contact  huangdijia@gmail.com
 */
namespace Hyperf\Utils;

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
     * Get the first item in the collection but throw an exception if no matching items exist.
     *
     * @param string $key
     * @param mixed $operator
     * @param mixed $value
     * @throws ItemNotFoundException
     * @return mixed
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
     * @throws ItemNotFoundException
     * @throws MultipleItemsFoundException
     * @return mixed
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
}
