<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */
use FriendsOfHyperf\Tests\ValidatedDTO\Datasets\ValidatedDTOInstance;
use FriendsOfHyperf\ValidatedDTO\Casting\ArrayCast;
use FriendsOfHyperf\ValidatedDTO\Casting\BooleanCast;
use FriendsOfHyperf\ValidatedDTO\Casting\DTOCast;
use FriendsOfHyperf\ValidatedDTO\Casting\IntegerCast;

it('properly casts from json string to array')
    ->expect(fn () => new ArrayCast())
    ->cast(test_property(), '{"name": "John Doe", "email": "john.doe@example.com"}')
    ->toBe(['name' => 'John Doe', 'email' => 'john.doe@example.com']);

it('properly casts from string to array')
    ->expect(fn () => new ArrayCast())
    ->cast(test_property(), 'Test')
    ->toBe(['Test']);

it('properly casts from integer to array')
    ->expect(fn () => new ArrayCast())
    ->cast(test_property(), 1)
    ->toBe([1]);

it('properly casts from array to array')
    ->expect(fn () => new ArrayCast())
    ->cast(test_property(), ['a', 'A', 1])
    ->toBe(['a', 'A', 1]);

it('properly casts a BooleanCast to array')
    ->expect(fn () => new ArrayCast(new BooleanCast()))
    ->cast(test_property(), [1, 'true', 'yes'])
    ->toBe([true, true, true]);

it('properly casts an IntegerCast to array')
    ->expect(fn () => new ArrayCast(new IntegerCast()))
    ->cast(test_property(), ['1', '5', '10'])
    ->toBe([1, 5, 10]);

it('properly casts an DTOCast', function () {
    $castable = new ArrayCast(new DTOCast(ValidatedDTOInstance::class));

    $johnDto = new ValidatedDTOInstance(['name' => 'John Doe', 'age' => 30]);
    $maryDto = new ValidatedDTOInstance(['name' => 'Mary Doe', 'age' => 25]);

    $dataToCast = [
        ['name' => 'John Doe', 'age' => 30],
        ['name' => 'Mary Doe', 'age' => 25],
    ];

    $result = $castable->cast(test_property(), $dataToCast);

    expect($result)->each->toBeInstanceOf(ValidatedDTOInstance::class);

    expect($result[0]->toArray())->toEqual($johnDto->toArray())
        ->and($result[1]->toArray())->toEqual($maryDto->toArray());
});
