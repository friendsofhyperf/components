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
        $data = ['name' => 'Hyperf', 'age' => 18];
        $rules = [
            'name' => 'required|string',
            'age' => 'required|integer',
        ];

        self::mockValidator($data, $rules);

        $dto = new class($data, $rules) extends ValidatedDTO {
            public function __construct(array $data, protected array $rules = [])
            {
                parent::__construct($data);
            }

            protected function rules(): array
            {
                return $this->rules;
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
