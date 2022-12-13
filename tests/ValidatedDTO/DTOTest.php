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
use Hyperf\Contract\ValidatorInterface;
use Hyperf\Utils\ApplicationContext;
use Hyperf\Utils\Arr;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;
use Mockery as m;
use Psr\Container\ContainerInterface;

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
    }

    public function testValidateWithScene()
    {
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
