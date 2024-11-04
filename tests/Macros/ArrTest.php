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

test('test shuffleAssoc', function () {
    $array = ['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4, 'e' => 5];
    $shuffled = Arr::shuffleAssoc($array);

    expect($shuffled)->not->toBe($array)
        ->toBeArray()
        ->toHaveKey('a')
        ->toHaveKey('b')
        ->toHaveKey('c')
        ->toHaveKey('d')
        ->toHaveKey('e')
        ->toHaveCount(5);
});

test('test shuffleAssoc with seed', function () {
    $array = ['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4, 'e' => 5];
    $shuffled = Arr::shuffleAssoc($array, 123);

    expect($shuffled)->not->toBe($array)
        ->toBeArray()
        ->toHaveKey('a')
        ->toHaveKey('b')
        ->toHaveKey('c')
        ->toHaveKey('d')
        ->toHaveKey('e')
        ->toHaveCount(5);
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
