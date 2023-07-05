<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */
use FriendsOfHyperf\Tests\ValidatedDTO\Datasets\AttributesDTO;
use FriendsOfHyperf\Tests\ValidatedDTO\Datasets\ModelCastInstance;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\ValidatorInterface;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;

beforeEach(function () {
    $this->mock(ValidatorFactoryInterface::class, function ($mock) {
        $mock->shouldReceive('make')->andReturn(Mockery::mock(ValidatorInterface::class, function ($mock) {
            $mock->shouldReceive('fails')->andReturn(false)
                ->shouldReceive('passes')->andReturn(true);
        }));
    });
    $this->mock(ConfigInterface::class, function ($mock) {
        $mock->shouldReceive('get')->with('dto')->andReturn([]);
    });
});

it('properly casts a Model property to a DTO class', function () {
    $model = new ModelCastInstance([
        'name' => faker()->name(),
        'metadata' => '{"age": 10, "doc": "foo"}',
    ]);

    expect($model->metadata)
        ->toBeInstanceOf(AttributesDTO::class);
});
