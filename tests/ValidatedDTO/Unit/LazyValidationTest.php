<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */
use FriendsOfHyperf\Tests\ValidatedDTO\Datasets\LazyDTO;
use FriendsOfHyperf\ValidatedDTO\ValidatedDTO;
use Hyperf\Contract\ValidatorInterface;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;
use Hyperf\Validation\ValidationException;

beforeEach(function () {
    $this->subject_name = faker()->name();
});

it('instantiates a ValidatedDTO marked as lazy without validating its data', function () {
    $validatedDTO = new LazyDTO(['name' => $this->subject_name]);

    expect($validatedDTO)->toBeInstanceOf(ValidatedDTO::class)
        ->and($validatedDTO->validatedData)
        ->toBe(['name' => $this->subject_name])
        ->and($validatedDTO->lazyValidation)
        ->toBeTrue();
});

it('does not fails a lazy validation with valid data', function () {
    $validatedDTO = new LazyDTO(['name' => $this->subject_name]);

    expect($validatedDTO)->toBeInstanceOf(ValidatedDTO::class)
        ->and($validatedDTO->validatedData)
        ->toBe(['name' => $this->subject_name])
        ->and($validatedDTO->lazyValidation)
        ->toBeTrue();

    $validatedDTO->validate();
});

it('fails a lazy validation with invalid data', function () {
    $this->mock(ValidatorFactoryInterface::class, function ($mock) {
        $mock->shouldReceive('make')->andReturn(Mockery::mock(ValidatorInterface::class, function ($mock) {
            $mock->shouldReceive('fails')->andReturn(true)
                ->shouldReceive('passes')->andReturn(false)
                ->shouldReceive('after')->andReturn(null);
        }));
    });

    $validatedDTO = new LazyDTO(['name' => null]);

    expect($validatedDTO)->toBeInstanceOf(ValidatedDTO::class)
        ->and($validatedDTO->validatedData)
        ->toBe(['name' => null])
        ->and($validatedDTO->lazyValidation)
        ->toBeTrue();

    $validatedDTO->validate();
})->throws(ValidationException::class);
