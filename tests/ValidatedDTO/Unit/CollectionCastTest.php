<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.0/README.md
 * @contact  huangdijia@gmail.com
 */
use FriendsOfHyperf\Tests\ValidatedDTO\Datasets\ValidatedDTOInstance;
use FriendsOfHyperf\ValidatedDTO\Casting\BooleanCast;
use FriendsOfHyperf\ValidatedDTO\Casting\CollectionCast;
use FriendsOfHyperf\ValidatedDTO\Casting\DTOCast;
use FriendsOfHyperf\ValidatedDTO\Casting\IntegerCast;
use FriendsOfHyperf\ValidatedDTO\ValidatedDTO;
use Hyperf\Collection\Collection;

it('casts to Collection class')
    ->expect(fn () => new CollectionCast())
    ->cast(test_property(), '{"name": "John Doe", "email": "john.doe@example.com"}')
    ->toBeInstanceOf(Collection::class);

it('properly casts a json string to collection')
    ->expect(fn () => new CollectionCast())
    ->cast(test_property(), '{"name": "John Doe", "email": "john.doe@example.com"}')
    ->toArray()
    ->toBe(['name' => 'John Doe', 'email' => 'john.doe@example.com']);

it('properly casts a string to collection')
    ->expect(fn () => new CollectionCast())
    ->cast(test_property(), 'Test')
    ->toArray()
    ->toBe(['Test']);

it('properly casts an integer to collection')
    ->expect(fn () => new CollectionCast())
    ->cast(test_property(), 1)
    ->toArray()
    ->toBe([1]);

it('properly casts an array to collection')
    ->expect(fn () => new CollectionCast())
    ->cast(test_property(), ['A', 1])
    ->toArray()
    ->toBe(['A', 1]);

it('properly casts a BooleanCast to Illuminate Collection class')
    ->expect(fn () => new CollectionCast(new BooleanCast()))
    ->cast(test_property(), [1, 'true', 'yes'])
    ->toBeInstanceOf(Collection::class);

it('properly casts a BooleanCast to collection')
    ->expect(fn () => new CollectionCast(new BooleanCast()))
    ->cast(test_property(), [1, 'true', 'yes'])
    ->toArray()
    ->toBe([true, true, true]);

it('properly casts an IntegerCast to Illuminate Collection class')
    ->expect(fn () => new CollectionCast(new IntegerCast()))
    ->cast(test_property(), ['1', '5', '10'])
    ->toBeInstanceOf(Collection::class);

it('properly casts an IntegerCast to collection')
    ->expect(fn () => new CollectionCast(new IntegerCast()))
    ->cast(test_property(), ['1', '5', '10'])
    ->toArray()
    ->toBe([1, 5, 10]);

it('properly casts an DTOCast', function () {
    $castable = new CollectionCast(new DTOCast(ValidatedDTOInstance::class));

    $johnDto = new ValidatedDTOInstance(['name' => 'John Doe', 'age' => 30]);
    $maryDto = new ValidatedDTOInstance(['name' => 'Mary Doe', 'age' => 25]);

    $dataToCast = [
        ['name' => 'John Doe', 'age' => 30],
        ['name' => 'Mary Doe', 'age' => 25],
    ];

    $result = $castable->cast(test_property(), $dataToCast);

    expect($result)->toBeInstanceOf(Collection::class);

    $result = $result->map(fn (ValidatedDTO $dto) => $dto->toArray())->toArray();

    expect($result)->toBe([$johnDto->toArray(), $maryDto->toArray()]);
});
