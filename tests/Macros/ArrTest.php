<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */
use Hyperf\Collection\Arr;

uses(\FriendsOfHyperf\Tests\TestCase::class)->group('macros', 'arr');

test('test isList', function ($expected, $value) {
    expect(Arr::isList($value))->toBe($expected);
})->with([
    [true, []],
    [true, [1, 2, 3]],
    [true, ['foo', 2, 3]],
    [true, ['foo', 'bar']],
    [true, [0 => 'foo', 'bar']],
    [true, [0 => 'foo', 1 => 'bar']],

    [false, [1 => 'foo', 'bar']],
    [false, [1 => 'foo', 0 => 'bar']],
    [false, [0 => 'foo', 'bar' => 'baz']],
    [false, [0 => 'foo', 2 => 'bar']],
    [false, ['foo' => 'bar', 'baz' => 'qux']],
]);

test('test keyBy', function () {
    $array = [
        ['id' => '123', 'data' => 'abc'],
        ['id' => '345', 'data' => 'def'],
        ['id' => '498', 'data' => 'hgi'],
    ];

    expect(Arr::keyBy($array, 'id'))->toBe([
        '123' => ['id' => '123', 'data' => 'abc'],
        '345' => ['id' => '345', 'data' => 'def'],
        '498' => ['id' => '498', 'data' => 'hgi'],
    ]);
});

test('test join', function ($expected, $args) {
    expect(Arr::join(...$args))->toBe($expected);
})->with([
    ['a, b, c', [['a', 'b', 'c'], ', ']],
    ['a, b and c', [['a', 'b', 'c'], ', ', ' and ']],
    ['a and b', [['a', 'b'], ', ', ' and ']],
    ['a', [['a'], ', ', ' and ']],
    ['', [[], ', ', ' and ']],
]);

test('test map', function () {
    $data = ['first' => 'taylor', 'last' => 'otwell'];
    $mapped = Arr::map($data, function ($value, $key) {
        return $key . '-' . strrev($value);
    });
    expect($mapped)->toBe(['first' => 'first-rolyat', 'last' => 'last-llewto']);
    expect($data)->toBe(['first' => 'taylor', 'last' => 'otwell']);
});

test('test prependKeysWith', function () {
    $array = [
        'id' => '123',
        'data' => '456',
        'list' => [1, 2, 3],
        'meta' => [
            'key' => 1,
        ],
    ];

    expect(Arr::prependKeysWith($array, 'test.'))->toBe([
        'test.id' => '123',
        'test.data' => '456',
        'test.list' => [1, 2, 3],
        'test.meta' => [
            'key' => 1,
        ],
    ]);
});

test('test sortByMany', function () {
    $unsorted = [
        ['name' => 'John', 'age' => 8, 'meta' => ['key' => 3]],
        ['name' => 'John', 'age' => 10, 'meta' => ['key' => 5]],
        ['name' => 'Dave', 'age' => 10, 'meta' => ['key' => 3]],
        ['name' => 'John', 'age' => 8, 'meta' => ['key' => 2]],
    ];

    // sort using keys
    expect(array_values(Arr::sortByMany($unsorted, [
        'name',
        'age',
        'meta.key',
    ])))->toBe([
        ['name' => 'Dave', 'age' => 10, 'meta' => ['key' => 3]],
        ['name' => 'John', 'age' => 8, 'meta' => ['key' => 2]],
        ['name' => 'John', 'age' => 8, 'meta' => ['key' => 3]],
        ['name' => 'John', 'age' => 10, 'meta' => ['key' => 5]],
    ]);

    // sort with order
    expect(array_values(Arr::sortByMany($unsorted, [
        'name',
        ['age', false],
        ['meta.key', true],
    ])))->toBe([
        ['name' => 'Dave', 'age' => 10, 'meta' => ['key' => 3]],
        ['name' => 'John', 'age' => 10, 'meta' => ['key' => 5]],
        ['name' => 'John', 'age' => 8, 'meta' => ['key' => 2]],
        ['name' => 'John', 'age' => 8, 'meta' => ['key' => 3]],
    ]);

    // sort using callable
    expect(array_values(Arr::sortByMany($unsorted, [
        function ($a, $b) {
            return $a['name'] <=> $b['name'];
        },
        function ($a, $b) {
            return $b['age'] <=> $a['age'];
        },
        ['meta.key', true],
    ])))->toBe([
        ['name' => 'Dave', 'age' => 10, 'meta' => ['key' => 3]],
        ['name' => 'John', 'age' => 10, 'meta' => ['key' => 5]],
        ['name' => 'John', 'age' => 8, 'meta' => ['key' => 2]],
        ['name' => 'John', 'age' => 8, 'meta' => ['key' => 3]],
    ]);
});

test('test sortDesc', function () {
    $unsorted = [
        ['name' => 'Chair'],
        ['name' => 'Desk'],
    ];

    $expected = [
        ['name' => 'Desk'],
        ['name' => 'Chair'],
    ];

    expect(array_values(Arr::sortDesc($unsorted)))->toBe($expected);

    // sort with closure
    expect(array_values(Arr::sortDesc($unsorted, function ($value) {
        return $value['name'];
    })))->toBe($expected);

    // sort with dot notation
    expect(array_values(Arr::sortDesc($unsorted, 'name')))->toBe($expected);
});

test('test undot', function () {
    expect(Arr::undot([
        'user.name' => 'Taylor',
        'user.age' => 25,
        'user.languages.0' => 'PHP',
        'user.languages.1' => 'C#',
    ]))->toBeArray()->toBe(['user' => ['name' => 'Taylor', 'age' => 25, 'languages' => ['PHP', 'C#']]]);

    expect(Arr::undot([
        'pagination.previous' => '<<',
        'pagination.next' => '>>',
    ]))->toBeArray()->toBe(['pagination' => ['previous' => '<<', 'next' => '>>']]);

    expect(Arr::undot([
        'foo',
        'foo.bar' => 'baz',
        'foo.baz' => ['a' => 'b'],
    ]))->toBeArray()->toBe(['foo', 'foo' => ['bar' => 'baz', 'baz' => ['a' => 'b']]]);
});
