<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.0/README.md
 * @contact  huangdijia@gmail.com
 */
use FriendsOfHyperf\Tests\ValidatedDTO\Datasets\AttributesDTO;
use FriendsOfHyperf\Tests\ValidatedDTO\Datasets\ModelCastInstance;
use Hyperf\Contract\ValidatorInterface;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;

it('properly casts a Model property to a DTO class', function () {
    $this->instance(ValidatorFactoryInterface::class, mocking(ValidatorFactoryInterface::class)->expect(
        make: fn () => mocking(ValidatorInterface::class)->expect(
            fails: fn () => false
        )
    ));

    $model = new ModelCastInstance([
        'name' => faker()->name(),
        'metadata' => '{"age": 10, "doc": "foo"}',
    ]);

    expect($model->metadata)
        ->toBeInstanceOf(AttributesDTO::class);
});
