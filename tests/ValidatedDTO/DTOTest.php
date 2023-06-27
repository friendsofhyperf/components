<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */
use FriendsOfHyperf\ValidatedDTO\Casting\StringCast;
use FriendsOfHyperf\ValidatedDTO\ValidatedDTO;
use Hyperf\Collection\Arr;
use Hyperf\Contract\ValidatorInterface;
use Hyperf\Stringable\Stringable;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;
use Mockery as m;

uses()->group('validated-dto');

afterEach(function () {
    m::close();
});

test('test BaseValidate', function () {
    $data = ['name' => 'Hyperf', 'age' => 18];

    $this->instance(ValidatorFactoryInterface::class, mocking(ValidatorFactoryInterface::class)->expect(
        make: fn () => mocking(ValidatorInterface::class)->expect(
            fails: fn () => false,
            validated: fn () => $data,
        ),
    ));

    $dto = UserDTO::fromArray($data);

    $this->assertSame('Hyperf', $dto->name);
    $this->assertSame(18, $dto->age);

    $dto = UserDTO::fromJson(json_encode($data));

    $this->assertSame('Hyperf', $dto->name);
    $this->assertSame(18, $dto->age);
});

test('test ValidateWithScene', function () {
    $data = ['foo' => 'Foo', 'bar' => 'Bar'];

    $this->mock(ValidatorFactoryInterface::class, function ($factory) use ($data) {
        $factory->shouldReceive('make')->andReturn(
            $this->mock(ValidatorInterface::class, function ($validator) use ($data) {
                $validator->shouldReceive('fails')->andReturn(false)
                    ->shouldReceive('validated')->andReturn(
                        Arr::only($data, ['foo']),
                        Arr::only($data, ['bar'])
                    );
            })
        );
    });

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

    $this->instance(ValidatorFactoryInterface::class, mocking(ValidatorFactoryInterface::class)->expect(
        make: fn () => mocking(ValidatorInterface::class)->expect(
            fails: fn () => false,
            validated: fn () => $data
        )
    ));

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
