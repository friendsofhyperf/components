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
use Hyperf\Utils\ApplicationContext;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;
use Hyperf\Validation\Validator;
use Mockery as m;
use Psr\Container\ContainerInterface;

/**
 * @internal
 * @coversNothing
 */
class DTOTest extends TestCase
{
    public function testValidate()
    {
        self::mockValidator(['name' => 'Hyperf', 'age' => 18], [
            'name' => 'required|string',
            'age' => 'required|integer',
        ]);

        $dto = new class(['name' => 'Hyperf', 'age' => 18]) extends ValidatedDTO {
            protected function rules(): array
            {
                return [
                    'name' => 'required|string',
                    'age' => 'required|integer',
                ];
            }

            public function defaults(): array
            {
                return [];
            }
        };

        $this->assertSame('Hyperf', $dto->name);
        $this->assertSame(18, $dto->age);
    }

    protected static function mockValidator($data = [], $rules = [])
    {
        $validator = m::mock(Validator::class)
            ->shouldReceive('fails')
            ->andReturn(false)
            ->shouldReceive('validated')
            ->andReturn($data)
            ->getMock();

        $factory = m::mock(ValidatorFactoryInterface::class)
            ->shouldReceive('make')
            ->with($data, $rules, [], [])
            ->andReturn(
                $validator
            )
            ->getMock();

        $container = m::mock(ContainerInterface::class)
            ->shouldReceive('get')
            ->with(ValidatorFactoryInterface::class)
            ->andReturn(
                $factory
            )
            ->getMock();

        ApplicationContext::setContainer($container);
    }
}
