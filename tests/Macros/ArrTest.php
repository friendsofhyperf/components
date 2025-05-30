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

require_once __DIR__ . '/Stubs/Common.php';

test('test arrayable', function () {
    $this->assertTrue(Arr::arrayable([]));
    $this->assertTrue(Arr::arrayable(new FriendsOfHyperf\Tests\Macros\Stubs\TestArrayableObject()));
    $this->assertTrue(Arr::arrayable(new FriendsOfHyperf\Tests\Macros\Stubs\TestJsonableObject()));
    $this->assertTrue(Arr::arrayable(new FriendsOfHyperf\Tests\Macros\Stubs\TestJsonSerializeObject()));
    $this->assertTrue(Arr::arrayable(new FriendsOfHyperf\Tests\Macros\Stubs\TestTraversableAndJsonSerializableObject()));

    $this->assertFalse(Arr::arrayable(null));
    $this->assertFalse(Arr::arrayable('abc'));
    $this->assertFalse(Arr::arrayable(new stdClass()));
    $this->assertFalse(Arr::arrayable((object) ['a' => 1, 'b' => 2]));
    $this->assertFalse(Arr::arrayable(123));
    $this->assertFalse(Arr::arrayable(12.34));
    $this->assertFalse(Arr::arrayable(true));
    $this->assertFalse(Arr::arrayable(new DateTime()));
    $this->assertFalse(Arr::arrayable(static fn () => null));
});

test('test getsAString', function () {
    $test_array = ['string' => 'foo bar', 'integer' => 1234];

    // Test string values are returned as strings
    $this->assertSame(
        'foo bar',
        Arr::string($test_array, 'string')
    );

    // Test that default string values are returned for missing keys
    $this->assertSame(
        'default',
        Arr::string($test_array, 'missing_key', 'default')
    );

    // Test that an exception is raised if the value is not a string
    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessageMatches('#^Array value for key \[integer\] must be a string, (.*) found.#');
    Arr::string($test_array, 'integer');
});

test('test getsAnInteger', function () {
    $test_array = ['string' => 'foo bar', 'integer' => 1234];

    // Test integer values are returned as integers
    $this->assertSame(
        1234,
        Arr::integer($test_array, 'integer')
    );

    // Test that default integer values are returned for missing keys
    $this->assertSame(
        999,
        Arr::integer($test_array, 'missing_key', 999)
    );

    // Test that an exception is raised if the value is not an integer
    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessageMatches('#^Array value for key \[string\] must be an integer, (.*) found.#');
    Arr::integer($test_array, 'string');
});

test('test getsAFloat', function () {
    $test_array = ['string' => 'foo bar', 'float' => 12.34];

    // Test float values are returned as floats
    $this->assertSame(
        12.34,
        Arr::float($test_array, 'float')
    );

    // Test that default float values are returned for missing keys
    $this->assertSame(
        56.78,
        Arr::float($test_array, 'missing_key', 56.78)
    );

    // Test that an exception is raised if the value is not a float
    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessageMatches('#^Array value for key \[string\] must be a float, (.*) found.#');
    Arr::float($test_array, 'string');
});

test('test from', function () {
    $this->assertSame(['foo' => 'bar'], Arr::from(['foo' => 'bar']));
    $this->assertSame(['foo' => 'bar'], Arr::from((object) ['foo' => 'bar']));
    $this->assertSame(['foo' => 'bar'], Arr::from(new FriendsOfHyperf\Tests\Macros\Stubs\TestArrayableObject()));
    $this->assertSame(['foo' => 'bar'], Arr::from(new FriendsOfHyperf\Tests\Macros\Stubs\TestJsonableObject()));
    $this->assertSame(['foo' => 'bar'], Arr::from(new FriendsOfHyperf\Tests\Macros\Stubs\TestJsonSerializeObject()));
    $this->assertSame(['foo'], Arr::from(new FriendsOfHyperf\Tests\Macros\Stubs\TestJsonSerializeWithScalarValueObject()));

    $this->assertSame(['name' => 'A'], Arr::from(TestEnum::A));
    $this->assertSame(['name' => 'A', 'value' => 1], Arr::from(TestBackedEnum::A));
    $this->assertSame(['name' => 'A', 'value' => 'A'], Arr::from(TestStringBackedEnum::A));

    $subject = [new stdClass(), new stdClass()];
    $items = new FriendsOfHyperf\Tests\Macros\Stubs\TestTraversableAndJsonSerializableObject($subject);
    $this->assertSame($subject, Arr::from($items));

    $items = new WeakMap();
    $items[$temp = new class {}] = 'bar';
    $this->assertSame(['bar'], Arr::from($items));

    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage('Items cannot be represented by a scalar value.');
    Arr::from(123);
});

test('test hasAll', function () {
    $array = ['name' => 'Deeka', 'age' => '', 'city' => null];
    $this->assertTrue(Arr::hasAll($array, 'name'));
    $this->assertTrue(Arr::hasAll($array, 'age'));
    $this->assertFalse(Arr::hasAll($array, ['age', 'car']));
    $this->assertTrue(Arr::hasAll($array, 'city'));
    $this->assertFalse(Arr::hasAll($array, ['city', 'some']));
    $this->assertTrue(Arr::hasAll($array, ['name', 'age', 'city']));
    $this->assertFalse(Arr::hasAll($array, ['name', 'age', 'city', 'country']));

    $array = ['user' => ['name' => 'Deeka']];
    $this->assertTrue(Arr::hasAll($array, 'user.name'));
    $this->assertFalse(Arr::hasAll($array, 'user.age'));

    $array = ['name' => 'Deeka', 'age' => '', 'city' => null];
    $this->assertFalse(Arr::hasAll($array, 'foo'));
    $this->assertFalse(Arr::hasAll($array, 'bar'));
    $this->assertFalse(Arr::hasAll($array, 'baz'));
    $this->assertFalse(Arr::hasAll($array, 'bah'));
    $this->assertFalse(Arr::hasAll($array, ['foo', 'bar', 'baz', 'bar']));
});

test('test getsABoolean', function () {
    $test_array = ['string' => 'foo bar', 'boolean' => true];

    // Test boolean values are returned as booleans
    $this->assertSame(
        true,
        Arr::boolean($test_array, 'boolean')
    );

    // Test that default boolean values are returned for missing keys
    $this->assertSame(
        true,
        Arr::boolean($test_array, 'missing_key', true)
    );

    // Test that an exception is raised if the value is not a boolean
    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessageMatches('#^Array value for key \[string\] must be a boolean, (.*) found.#');
    Arr::boolean($test_array, 'string');
});

test('test getsAnArray', function () {
    $test_array = ['string' => 'foo bar', 'array' => ['foo', 'bar']];

    // Test array values are returned as arrays
    $this->assertSame(
        ['foo', 'bar'],
        Arr::array($test_array, 'array')
    );

    // Test that default array values are returned for missing keys
    $this->assertSame(
        [1, 'two'],
        Arr::array($test_array, 'missing_key', [1, 'two'])
    );

    // Test that an exception is raised if the value is not an array
    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessageMatches('#^Array value for key \[string\] must be an array, (.*) found.#');
    Arr::array($test_array, 'string');
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
