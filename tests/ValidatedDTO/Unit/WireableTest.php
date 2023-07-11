<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.0/README.md
 * @contact  huangdijia@gmail.com
 */
use FriendsOfHyperf\Tests\ValidatedDTO\Datasets\SimpleNameDTO;
use FriendsOfHyperf\Tests\ValidatedDTO\Datasets\WireableDTO;
use FriendsOfHyperf\ValidatedDTO\SimpleDTO;
use Hyperf\Collection\Collection;

use function Hyperf\Collection\collect;

it('validates that a Wireable DTO will return the correct data for the toLivewire method', function () {
    $wireableDTO = new WireableDTO(['name' => $this->name, 'age' => $this->age]);

    expect($wireableDTO)->toBeInstanceOf(SimpleDTO::class)
        ->and($wireableDTO->validatedData)
        ->toBe(['name' => $this->name, 'age' => $this->age])
        ->and($wireableDTO->toLivewire())
        ->toBe($wireableDTO->toArray());
});

it('validates that a Wireable DTO with a nested DTO will return the correct data for the toLivewire method', function () {
    $data = [
        'name' => $this->name,
        'age' => $this->age,
        'simple_name_dto' => [
            'first_name' => $this->name,
            'last_name' => $this->name,
        ],
    ];

    $wireableDTO = new WireableDTO($data);

    expect($wireableDTO)
        ->toBeInstanceOf(SimpleDTO::class)
        ->and($wireableDTO->simple_name_dto)
        ->toBeInstanceOf(SimpleNameDTO::class)
        ->and($wireableDTO->toLivewire())
        ->toBe($data);
});

it('validates that a Wireable DTO with a DTO Collection will return the correct data for the toLivewire method', function () {
    $simple_name = [
        'first_name' => $this->name,
        'last_name' => $this->name,
    ];

    $simple_name_2 = [
        'first_name' => $this->name,
        'last_name' => $this->name,
    ];

    $data = [
        'name' => $this->name,
        'age' => $this->age,
        'simple_names_collection' => [$simple_name, $simple_name_2],
    ];

    $wireableDTO = new WireableDTO($data);

    expect($wireableDTO)
        ->toBeInstanceOf(SimpleDTO::class)
        ->and($wireableDTO->simple_names_collection)
        ->toBeInstanceOf(Collection::class)
        ->and($wireableDTO->simple_names_collection->first())
        ->toBeInstanceOf(SimpleNameDTO::class)
        ->and($wireableDTO->toLivewire())
        ->toBe($data);
});

it('validates that a Wireable DTO can be instantiated with the fromLivewire method with array', function () {
    $wireableDTO = WireableDTO::fromLivewire(['name' => $this->name, 'age' => $this->age]);

    expect($wireableDTO)
        ->toBeInstanceOf(SimpleDTO::class)
        ->and($wireableDTO->validatedData)
        ->toBe(['name' => $this->name, 'age' => $this->age])
        ->and($wireableDTO->toLivewire())
        ->toBe($wireableDTO->toArray());
});

it('validates that a Wireable DTO can be instantiated with the fromLivewire method with collection', function () {
    $wireableDTO = WireableDTO::fromLivewire(collect(['name' => $this->name, 'age' => $this->age]));

    expect($wireableDTO)
        ->toBeInstanceOf(SimpleDTO::class)
        ->and($wireableDTO->validatedData)
        ->toBe(['name' => $this->name, 'age' => $this->age])
        ->and($wireableDTO->toLivewire())
        ->toBe($wireableDTO->toArray());
});

it('validates that a Wireable DTO can be instantiated with the fromLivewire method with object', function () {
    $wireableDTO = WireableDTO::fromLivewire((object) ['name' => $this->name, 'age' => $this->age]);

    expect($wireableDTO)
        ->toBeInstanceOf(SimpleDTO::class)
        ->and($wireableDTO->validatedData)
        ->toBe(['name' => $this->name, 'age' => $this->age])
        ->and($wireableDTO->toLivewire())
        ->toBe($wireableDTO->toArray());
});

it('validates that a Wireable DTO will be empty when instantiated with the fromLivewire method with invalid values', function ($value) {
    $wireableDTO = WireableDTO::fromLivewire($value);

    expect($wireableDTO)
        ->toBeInstanceOf(SimpleDTO::class)
        ->and($wireableDTO->validatedData)
        ->toBe([])
        ->and($wireableDTO->toLivewire())
        ->toBe([]);
})->with([
    null,
    true,
    false,
    'string',
    10.5,
    10,
    new stdClass(),
]);
