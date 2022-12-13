<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.x/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\Tests\ValidatedDTO;

use FriendsOfHyperf\Tests\TestCase;
use FriendsOfHyperf\ValidatedDTO\ValidatedDTO;
use Hyperf\Context\Context;
use Hyperf\Contract\ValidatorInterface;
use Hyperf\Utils\Arr;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;
use Mockery as m;

/**
 * @internal
 * @coversNothing
 */
class DTOTest extends TestCase
{
    public function tearDown(): void
    {
        m::close();
    }

    public function testBaseValidate()
    {
        $data = ['name' => 'Hyperf', 'age' => 18];

        Context::set(
            ValidatedDTO::class . ':validatorFactory',
            m::mock(ValidatorFactoryInterface::class, function ($mock) use ($data) {
                $mock->shouldReceive('make')->andReturn(
                    m::mock(ValidatorInterface::class, function ($mock) use ($data) {
                        $mock->shouldReceive('fails')->andReturn(false);
                        $mock->shouldReceive('validated')->andReturn($data);
                    })
                );
            })
        );

        $dto = UserDTO::fromArray($data);

        $this->assertSame('Hyperf', $dto->name);
        $this->assertSame(18, $dto->age);

        $dto = UserDTO::fromJson(json_encode($data));

        $this->assertSame('Hyperf', $dto->name);
        $this->assertSame(18, $dto->age);
    }

    public function testValidateWithScene()
    {
        $data = ['foo' => 'Foo', 'bar' => 'Bar'];

        Context::set(
            ValidatedDTO::class . ':validatorFactory',
            m::mock(ValidatorFactoryInterface::class, function ($mock) use ($data) {
                $mock->shouldReceive('make')->andReturn(
                    m::mock(ValidatorInterface::class, function ($mock) use ($data) {
                        $mock->shouldReceive('fails')->andReturn(false);
                        $mock->shouldReceive('validated')->andReturn(
                            Arr::only($data, ['foo']),
                            Arr::only($data, ['bar'])
                        );
                    })
                );
            })
        );

        $dto = FooDTO::fromArray($data, 'foo');

        $this->assertSame('Foo', $dto->foo);
        $this->assertNull($dto->bar);

        $dto = FooDTO::fromArray($data, 'bar');

        $this->assertNull($dto->foo);
        $this->assertSame('Bar', $dto->bar);
    }
}

class UserDTO extends ValidatedDTO
{
    protected array $rules = [
        'name' => 'required|string',
        'age' => 'required|integer',
    ];
}

class FooDTO extends ValidatedDTO
{
    protected array $rules = [
        'foo' => 'required|string',
        'bar' => 'required|string',
    ];

    protected array $scenes = [
        'foo' => ['foo'],
        'bar' => ['bar'],
    ];
}
