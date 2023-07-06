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
use FriendsOfHyperf\ValidatedDTO\Casting\DTOCast;
use FriendsOfHyperf\ValidatedDTO\Exception\CastException;
use FriendsOfHyperf\ValidatedDTO\Exception\CastTargetException;
use FriendsOfHyperf\ValidatedDTO\ValidatedDTO;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\ValidatorInterface;
use Hyperf\Database\Model\Model;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;
use Mockery;

beforeEach(function () {
    $this->mock(
        ValidatorFactoryInterface::class,
        function ($mock) {
            $mock->shouldReceive('make')->andReturn(Mockery::mock(ValidatorInterface::class, function ($mock) {
                $mock->shouldReceive('fails')->andReturn(false)
                    ->shouldReceive('passes')->andReturn(true);
            }));
        }
    );
    $this->mock(ConfigInterface::class, function ($mock) {
        $mock->shouldReceive('get')->with('dto')->andReturn([]);
    });
});

it('casts to DTO', function () {
    $castable = new DTOCast(ValidatedDTOInstance::class);

    expect($castable)->cast(test_property(), '{"name": "John Doe", "age": 30}')
        ->toBeInstanceOf(ValidatedDTO::class)
        ->and($castable)->cast(test_property(), '{"name": "John Doe", "age": 30}')
        ->toArray()
        ->toBe(['name' => 'John Doe', 'age' => 30])
        ->and($castable)->cast(test_property(), ['name' => 'John Doe', 'age' => 30])
        ->toBeInstanceOf(ValidatedDTO::class)
        ->and($castable)->cast(test_property(), ['name' => 'John Doe', 'age' => 30])
        ->toArray()
        ->toEqual(['name' => 'John Doe', 'age' => 30]);

    $this->expectException(CastException::class);
    $castable->cast(test_property(), 'TEST');

    $castable = new DTOCast(Model::class);

    $this->expectException(CastTargetException::class);
    $castable->cast(test_property(), ['name' => 'John Doe', 'age' => 30]);
});
