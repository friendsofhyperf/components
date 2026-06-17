<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Sentry\Util;

use ArrayAccess;
use ArrayIterator;
use ArrayObject;
use Countable;
use IteratorAggregate;
use Traversable;

class CoArrayObject implements ArrayAccess, Countable, IteratorAggregate
{
    public function offsetExists($offset): bool
    {
        return isset($this->getArrayObject()[$offset]);
    }

    public function offsetGet($offset): mixed
    {
        return $this->getArrayObject()[$offset] ?? null;
    }

    public function offsetSet($offset, $value): void
    {
        $this->getArrayObject()[$offset] = $value;
    }

    public function offsetUnset($offset): void
    {
        unset($this->getArrayObject()[$offset]);
    }

    public function count(): int
    {
        return count($this->getArrayObject());
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->getArrayObject() ?? []);
    }

    private function getArrayObject(?int $id = null): ?ArrayObject
    {
        return \Hyperf\Engine\Coroutine::getContextFor($id);
    }
}
