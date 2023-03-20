<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.x/README.md
 * @contact  huangdijia@gmail.com
 */
use Hyperf\Utils\Arr;

uses(\FriendsOfHyperf\Tests\TestCase::class);

test('test IsList', function () {
    $this->assertTrue(Arr::isList([]));
    $this->assertTrue(Arr::isList([1, 2, 3]));
    $this->assertTrue(Arr::isList(['foo', 2, 3]));
    $this->assertTrue(Arr::isList(['foo', 'bar']));
    $this->assertTrue(Arr::isList([0 => 'foo', 'bar']));
    $this->assertTrue(Arr::isList([0 => 'foo', 1 => 'bar']));

    $this->assertFalse(Arr::isList([1 => 'foo', 'bar']));
    $this->assertFalse(Arr::isList([1 => 'foo', 0 => 'bar']));
    $this->assertFalse(Arr::isList([0 => 'foo', 'bar' => 'baz']));
    $this->assertFalse(Arr::isList([0 => 'foo', 2 => 'bar']));
    $this->assertFalse(Arr::isList(['foo' => 'bar', 'baz' => 'qux']));
});

test('test KeyBy', function () {
    $array = [
        ['id' => '123', 'data' => 'abc'],
        ['id' => '345', 'data' => 'def'],
        ['id' => '498', 'data' => 'hgi'],
    ];

    $this->assertEquals([
        '123' => ['id' => '123', 'data' => 'abc'],
        '345' => ['id' => '345', 'data' => 'def'],
        '498' => ['id' => '498', 'data' => 'hgi'],
    ], Arr::keyBy($array, 'id'));
});

test('test Join', function () {
    $this->assertSame('a, b, c', Arr::join(['a', 'b', 'c'], ', '));
    $this->assertSame('a, b and c', Arr::join(['a', 'b', 'c'], ', ', ' and '));
    $this->assertSame('a and b', Arr::join(['a', 'b'], ', ', ' and '));
    $this->assertSame('a', Arr::join(['a'], ', ', ' and '));
    $this->assertSame('', Arr::join([], ', ', ' and '));
});

test('test Map', function () {
    $data = ['first' => 'taylor', 'last' => 'otwell'];
    $mapped = Arr::map($data, function ($value, $key) {
        return $key . '-' . strrev($value);
    });
    $this->assertEquals(['first' => 'first-rolyat', 'last' => 'last-llewto'], $mapped);
    $this->assertEquals(['first' => 'taylor', 'last' => 'otwell'], $data);
});

test('test PrependKeysWith', function () {
    $array = [
        'id' => '123',
        'data' => '456',
        'list' => [1, 2, 3],
        'meta' => [
            'key' => 1,
        ],
    ];

    $this->assertEquals([
        'test.id' => '123',
        'test.data' => '456',
        'test.list' => [1, 2, 3],
        'test.meta' => [
            'key' => 1,
        ],
    ], Arr::prependKeysWith($array, 'test.'));
});

test('test SortByMany', function () {
    $unsorted = [
        ['name' => 'John', 'age' => 8, 'meta' => ['key' => 3]],
        ['name' => 'John', 'age' => 10, 'meta' => ['key' => 5]],
        ['name' => 'Dave', 'age' => 10, 'meta' => ['key' => 3]],
        ['name' => 'John', 'age' => 8, 'meta' => ['key' => 2]],
    ];

    // sort using keys
    $sorted = array_values(Arr::sortByMany($unsorted, [
        'name',
        'age',
        'meta.key',
    ]));
    $this->assertEquals([
        ['name' => 'Dave', 'age' => 10, 'meta' => ['key' => 3]],
        ['name' => 'John', 'age' => 8, 'meta' => ['key' => 2]],
        ['name' => 'John', 'age' => 8, 'meta' => ['key' => 3]],
        ['name' => 'John', 'age' => 10, 'meta' => ['key' => 5]],
    ], $sorted);

    // sort with order
    $sortedWithOrder = array_values(Arr::sortByMany($unsorted, [
        'name',
        ['age', false],
        ['meta.key', true],
    ]));
    $this->assertEquals([
        ['name' => 'Dave', 'age' => 10, 'meta' => ['key' => 3]],
        ['name' => 'John', 'age' => 10, 'meta' => ['key' => 5]],
        ['name' => 'John', 'age' => 8, 'meta' => ['key' => 2]],
        ['name' => 'John', 'age' => 8, 'meta' => ['key' => 3]],
    ], $sortedWithOrder);

    // sort using callable
    $sortedWithCallable = array_values(Arr::sortByMany($unsorted, [
        function ($a, $b) {
            return $a['name'] <=> $b['name'];
        },
        function ($a, $b) {
            return $b['age'] <=> $a['age'];
        },
        ['meta.key', true],
    ]));
    $this->assertEquals([
        ['name' => 'Dave', 'age' => 10, 'meta' => ['key' => 3]],
        ['name' => 'John', 'age' => 10, 'meta' => ['key' => 5]],
        ['name' => 'John', 'age' => 8, 'meta' => ['key' => 2]],
        ['name' => 'John', 'age' => 8, 'meta' => ['key' => 3]],
    ], $sortedWithCallable);
});

test('test SortDesc', function () {
    $unsorted = [
        ['name' => 'Chair'],
        ['name' => 'Desk'],
    ];

    $expected = [
        ['name' => 'Desk'],
        ['name' => 'Chair'],
    ];

    $sorted = array_values(Arr::sortDesc($unsorted));
    $this->assertEquals($expected, $sorted);

    // sort with closure
    $sortedWithClosure = array_values(Arr::sortDesc($unsorted, function ($value) {
        return $value['name'];
    }));
    $this->assertEquals($expected, $sortedWithClosure);

    // sort with dot notation
    $sortedWithDotNotation = array_values(Arr::sortDesc($unsorted, 'name'));
    $this->assertEquals($expected, $sortedWithDotNotation);
});

test('test Undot', function () {
    $array = Arr::undot([
        'user.name' => 'Taylor',
        'user.age' => 25,
        'user.languages.0' => 'PHP',
        'user.languages.1' => 'C#',
    ]);
    $this->assertEquals(['user' => ['name' => 'Taylor', 'age' => 25, 'languages' => ['PHP', 'C#']]], $array);

    $array = Arr::undot([
        'pagination.previous' => '<<',
        'pagination.next' => '>>',
    ]);
    $this->assertEquals(['pagination' => ['previous' => '<<', 'next' => '>>']], $array);

    $array = Arr::undot([
        'foo',
        'foo.bar' => 'baz',
        'foo.baz' => ['a' => 'b'],
    ]);
    $this->assertEquals(['foo', 'foo' => ['bar' => 'baz', 'baz' => ['a' => 'b']]], $array);
});
