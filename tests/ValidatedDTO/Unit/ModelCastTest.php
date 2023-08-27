<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */
use FriendsOfHyperf\Tests\ValidatedDTO\Datasets\ModelInstance;
use FriendsOfHyperf\Tests\ValidatedDTO\Datasets\ValidatedDTOInstance;
use FriendsOfHyperf\ValidatedDTO\Casting\ModelCast;
use FriendsOfHyperf\ValidatedDTO\Exception\CastTargetException;
use Hyperf\Database\Model\Model;

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
