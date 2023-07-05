<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.0/README.md
 * @contact  huangdijia@gmail.com
 */
use FriendsOfHyperf\Tests\ValidatedDTO\Datasets\ModelInstance;
use FriendsOfHyperf\Tests\ValidatedDTO\Datasets\ValidatedDTOInstance;
use FriendsOfHyperf\ValidatedDTO\Casting\ModelCast;
use FriendsOfHyperf\ValidatedDTO\Exception\CastTargetException;
use Hyperf\Contract\ValidatorInterface;
use Hyperf\Database\Model\Model;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;

beforeEach(function () {
    $this->instance(
        ValidatorFactoryInterface::class,
        Mockery::mock(ValidatorFactoryInterface::class, function ($mock) {
            $mock->shouldReceive('make')->andReturn(Mockery::mock(ValidatorInterface::class, function ($mock) {
                $mock->shouldReceive('fails')->andReturn(false)
                    ->shouldReceive('passes')->andReturn(true);
            }));
        })
    );
});

it('properly casts a to the Model class')
    ->expect(fn () => new ModelCast(ModelInstance::class))
    ->cast(test_property(), '{"name": "John Doe", "age": 30}')
    ->toBeInstanceOf(Model::class);

it('properly casts a json string to model')
    ->expect(fn () => new ModelCast(ModelInstance::class))
    ->cast(test_property(), '{"name": "John Doe", "age": 30}')
    ->toArray()
    ->toBe(['name' => 'John Doe', 'age' => 30]);

it('properly casts an array to the Model class')
    ->expect(fn () => new ModelCast(ModelInstance::class))
    ->cast(test_property(), ['name' => 'John Doe', 'age' => 30])
    ->toBeInstanceOf(Model::class);

it('properly casts an array string to model')
    ->expect(fn () => new ModelCast(ModelInstance::class))
    ->cast(test_property(), ['name' => 'John Doe', 'age' => 30])
    ->toArray()
    ->toBe(['name' => 'John Doe', 'age' => 30]);

it('throws exception when  the property has an invalid cast configuration')
    ->expect(fn () => new ModelCast(ValidatedDTOInstance::class))
    ->cast(test_property(), ['name' => 'John Doe', 'age' => 30])
    ->throws(CastTargetException::class, 'The property: test_property has an invalid cast configuration');
