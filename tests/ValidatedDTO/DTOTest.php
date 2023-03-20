<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.x/README.md
 * @contact  huangdijia@gmail.com
 */
use FriendsOfHyperf\ValidatedDTO\Casting\StringCast;
use FriendsOfHyperf\ValidatedDTO\ValidatedDTO;
use Hyperf\Contract\ValidatorInterface;
use Hyperf\Utils\ApplicationContext;
use Hyperf\Utils\Arr;
use Hyperf\Utils\Stringable;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;
use Mockery as m;
use Psr\Container\ContainerInterface;

afterEach(function () {
    m::close();
});

test('test BaseValidate', function () {
    $data = ['name' => 'Hyperf', 'age' => 18];

    ApplicationContext::setContainer(
        m::mock(ContainerInterface::class, [
            'get' => m::mock(ValidatorFactoryInterface::class, [
                'make' => m::mock(ValidatorInterface::class, ['fails' => false, 'validated' => $data]),
            ]),
        ])
    );

    $dto = UserDTO::fromArray($data);

    $this->assertSame('Hyperf', $dto->name);
    $this->assertSame(18, $dto->age);

    $dto = UserDTO::fromJson(json_encode($data));

    $this->assertSame('Hyperf', $dto->name);
    $this->assertSame(18, $dto->age);
});

test('test ValidateWithScene', function () {
    $data = ['foo' => 'Foo', 'bar' => 'Bar'];

    ApplicationContext::setContainer(
        m::mock(ContainerInterface::class, [
            'get' => m::mock(ValidatorFactoryInterface::class, [
                'make' => m::mock(ValidatorInterface::class, function ($mock) use ($data) {
                    $mock->shouldReceive('fails')->andReturn(false)
                        ->shouldReceive('validated')->andReturn(
                            Arr::only($data, ['foo']),
                            Arr::only($data, ['bar'])
                        );
                }),
            ]),
        ]),
    );

    $dto = FooDTO::fromArray($data, 'foo');

    $this->assertSame('Foo', $dto->foo);
    $this->assertNull($dto->bar);

    $dto = FooDTO::fromArray($data, 'bar');

    $this->assertNull($dto->foo);
    $this->assertSame('Bar', $dto->bar);
});

test('test ValidateWithCasting', function () {
    $data = [
        'foo' => new Stringable('Foo'),
        'bar' => new Stringable('Bar'),
    ];

    ApplicationContext::setContainer(
        m::mock(ContainerInterface::class, [
            'get' => m::mock(ValidatorFactoryInterface::class, [
                'make' => m::mock(ValidatorInterface::class, [
                    'fails' => false,
                    'validated' => $data,
                ]),
            ]),
        ]),
    );

    $dto = BarDTO::fromArray($data);

    $this->assertSame('Foo', $dto->foo);
    $this->assertSame('Bar', $dto->bar);
});

class UserDTO extends ValidatedDTO
{
    protected function rules(): array
    {
        return [
            'name' => 'required|string',
            'age' => 'required|integer',
        ];
    }
}

class FooDTO extends ValidatedDTO
{
    protected function rules(): array
    {
        return [
            'foo' => 'required|string',
            'bar' => 'required|string',
        ];
    }

    protected function scenes(): array
    {
        return [
            'foo' => ['foo'],
            'bar' => ['bar'],
        ];
    }
}

class BarDTO extends ValidatedDTO
{
    protected function rules(): array
    {
        return [
            'foo' => 'required|string',
            'bar' => 'required|string',
        ];
    }

    protected function casts(): array
    {
        return [
            'foo' => new StringCast(),
            'bar' => new StringCast(),
        ];
    }
}
