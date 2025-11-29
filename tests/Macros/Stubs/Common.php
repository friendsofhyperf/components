<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Tests\Macros\Stubs;

use ArrayIterator;
use Hyperf\Contract\Arrayable;
use Hyperf\Contract\Jsonable;
use IteratorAggregate;
use JsonSerializable;
use Stringable;
use Traversable;

class TestArrayableObject implements Arrayable
{
    public function toArray(): array
    {
        return ['foo' => 'bar'];
    }
}

class TestJsonableObject implements Stringable, Jsonable
{
    public function __toString(): string
    {
        return '{"foo":"bar"}';
    }

    public function toJson($options = 0): string
    {
        return '{"foo":"bar"}';
    }
}

class TestJsonSerializeObject implements JsonSerializable
{
    public function jsonSerialize(): array
    {
        return ['foo' => 'bar'];
    }
}

class TestJsonSerializeWithScalarValueObject implements JsonSerializable
{
    public function jsonSerialize(): string
    {
        return 'foo';
    }
}

class TestTraversableAndJsonSerializableObject implements IteratorAggregate, JsonSerializable
{
    public $items;

    public function __construct($items = [])
    {
        $this->items = $items;
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->items);
    }

    public function jsonSerialize(): array
    {
        return json_decode(json_encode($this->items), true);
    }
}
