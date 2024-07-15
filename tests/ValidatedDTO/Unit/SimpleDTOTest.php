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
use FriendsOfHyperf\Tests\ValidatedDTO\Datasets\CallableCastingDTOInstance;
use FriendsOfHyperf\Tests\ValidatedDTO\Datasets\SimpleDTOInstance;
use FriendsOfHyperf\Tests\ValidatedDTO\Datasets\SimpleMapBeforeExportDTO;
use FriendsOfHyperf\Tests\ValidatedDTO\Datasets\SimpleMapBeforeValidationDTO;
use FriendsOfHyperf\Tests\ValidatedDTO\Datasets\SimpleMapDataDTO;
use FriendsOfHyperf\Tests\ValidatedDTO\Datasets\SimpleMappedNameDTO;
use FriendsOfHyperf\Tests\ValidatedDTO\Datasets\SimpleNameDTO;
use FriendsOfHyperf\Tests\ValidatedDTO\Datasets\SimpleNullableDTO;
use FriendsOfHyperf\Tests\ValidatedDTO\Datasets\SimpleUserDTO;
use FriendsOfHyperf\Tests\ValidatedDTO\Datasets\User;
use FriendsOfHyperf\ValidatedDTO\Exception\InvalidJsonException;
use FriendsOfHyperf\ValidatedDTO\SimpleDTO;
use Hyperf\Command\Command;
use Hyperf\Database\Model\Model;
use Mockery as m;
use Psr\Http\Message\RequestInterface;
use Symfony\Component\Console\Input\InputInterface;

it('instantiates a SimpleDTO', function () {
    $simpleDTO = new SimpleDTOInstance(['name' => $this->subject_name]);

    expect($simpleDTO)->toBeInstanceOf(SimpleDTO::class)
        ->and($simpleDTO->validatedData)
        ->toBe(['name' => $this->subject_name]);
});

it('instantiates a SimpleDTO with nullable and optional properties', function () {
    $dto = new SimpleNullableDTO([
        'name' => $this->subject_name,
        'address' => null,
    ]);

    expect($dto)->toBeInstanceOf(SimpleNullableDTO::class)
        ->and($dto->name)
        ->toBeString()
        ->and($dto->age)
        ->toBeNull()
        ->and($dto->address)
        ->toBeNull();
});

it('returns null when trying to access a property that does not exist', function () {
    $simpleDTO = new SimpleDTOInstance(['name' => $this->subject_name]);

    expect($simpleDTO->age)->toBeNull();
});

it('validates that is possible to set a property in a SimpleDTO', function () {
    $simpleDTO = new SimpleDTOInstance(['name' => $this->subject_name]);

    $simpleDTO->age = 30;

    expect($simpleDTO->age)->toBe(30);
});

it('validates that a SimpleDTO can be instantiated from a JSON string', function () {
    $simpleDTO = SimpleDTOInstance::fromJson('{"name": "' . $this->subject_name . '"}');

    expect($simpleDTO->validatedData)
        ->toBe(['name' => $this->subject_name]);
});

it('throws exception when trying to instantiate a SimpleDTO from an invalid JSON string')
    ->expect(fn () => SimpleDTOInstance::fromJson('{"name": "' . $this->subject_name . '"'))
    ->throws(InvalidJsonException::class);

it('validates that a SimpleDTO can be instantiated from Array', function () {
    $simpleDTO = SimpleDTOInstance::fromArray(['name' => $this->subject_name]);

    expect($simpleDTO->validatedData)
        ->toBe(['name' => $this->subject_name]);
});

it('validates that a SimpleDTO can be instantiated from a Request', function () {
    /** @var RequestInterface $request */
    $request = m::mock(RequestInterface::class, [
        'all' => ['name' => $this->subject_name],
    ]);

    $simpleDTO = SimpleDTOInstance::fromRequest($request);

    expect($simpleDTO->validatedData)
        ->toBe(['name' => $this->subject_name]);
});

it('validates that a SimpleDTO can be instantiated from an Database Model', function () {
    $model = new class() extends Model {
        protected array $fillable = ['name'];
    };

    $model->fill(['name' => $this->subject_name]);

    $simpleDTO = SimpleDTOInstance::fromModel($model);

    expect($simpleDTO->validatedData)
        ->toBe(['name' => $this->subject_name]);
});

it('validates that a SimpleDTO can be instantiated from Command arguments', function () {
    $command = new class($this->subject_name) extends Command {
        public function __construct(protected string $subject_name)
        {
            $this->input = m::mock(InputInterface::class, [
                'getArguments' => ['name' => $this->subject_name],
            ]);
        }
    };

    $simpleDTO = SimpleDTOInstance::fromCommandArguments($command);

    expect($simpleDTO->validatedData)
        ->toHaveKey('name', $this->subject_name);
});

it('validates that a SimpleDTO can be instantiated from Command options', function () {
    $command = new class($this->subject_name) extends Command {
        public function __construct(protected string $subject_name)
        {
            $this->input = m::mock(InputInterface::class, [
                'getOptions' => ['name' => $this->subject_name],
            ]);
        }
    };

    $simpleDTO = SimpleDTOInstance::fromCommandOptions($command);

    expect($simpleDTO->validatedData)
        ->toHaveKey('name', $this->subject_name);
});

it('validates that a SimpleDTO can be instantiated from a Command', function () {
    $command = new class($this->subject_name) extends Command {
        public function __construct(protected string $subject_name)
        {
            $this->input = m::mock(InputInterface::class, [
                'getArguments' => ['name' => $this->subject_name],
                'getOptions' => ['age' => 30],
            ]);
        }
    };

    $simpleDTO = SimpleDTOInstance::fromCommand($command);

    expect($simpleDTO->validatedData)
        ->toHaveKey('name', $this->subject_name)
        ->toHaveKey('age', 30);
});

it('validates that the SimpleDTO can be converted into an array', function () {
    $simpleDTO = new SimpleDTOInstance(['name' => $this->subject_name]);

    expect($simpleDTO)->toArray()
        ->toBe(['name' => $this->subject_name]);
});

it('validates that the SimpleDTO can be converted into a JSON string', function () {
    $simpleDTO = new SimpleDTOInstance(['name' => $this->subject_name]);

    expect($simpleDTO)->toJson()
        ->toBe('{"name":"' . $this->subject_name . '"}');
});

it('validates that the SimpleDTO can be converted into a pretty JSON string', function () {
    $simpleDTO = new SimpleDTOInstance(['name' => $this->subject_name]);

    expect($simpleDTO)->toPrettyJson()
        ->toBe(json_encode(['name' => $this->subject_name], JSON_PRETTY_PRINT));
});

it('validates that the SimpleDTO can be converted into an Database Model', function () {
    $simpleDTO = new SimpleDTOInstance(['name' => $this->subject_name]);

    $model = new class() extends Model {
        protected array $fillable = ['name'];
    };

    $model_instance = $simpleDTO->toModel($model::class);

    expect($model_instance)
        ->toBeInstanceOf(Model::class)
        ->toArray()
        ->toBe(['name' => $this->subject_name]);
});

it('maps data before validation', function () {
    $dto = SimpleMapBeforeValidationDTO::fromArray(['full_name' => $this->subject_name]);

    expect($dto->full_name)
        ->toBeNull()
        ->and($dto->name)
        ->toBe($this->subject_name);
});

it('maps data before export', function () {
    $dto = SimpleMapBeforeExportDTO::fromArray(['name' => $this->subject_name]);

    expect($dto->name)
        ->toBe($this->subject_name)
        ->and($dto->username)
        ->toBeNull()
        ->and($dto->toArray())
        ->toBe(['username' => $this->subject_name]);
});

it('maps data before validation and before export', function () {
    $dto = SimpleMapDataDTO::fromArray(['full_name' => $this->subject_name]);

    expect($dto->full_name)
        ->toBeNull()
        ->and($dto->name)
        ->toBe($this->subject_name)
        ->and($dto->username)
        ->toBeNull()
        ->and($dto->toArray())
        ->toBe(['username' => $this->subject_name]);
});

it('maps nested data to flat data before validation', function () {
    $dto = SimpleMappedNameDTO::fromArray([
        'name' => [
            'first_name' => 'John',
            'last_name' => 'Doe',
        ],
    ]);

    expect($dto->first_name)
        ->toBe('John')
        ->and($dto->last_name)
        ->toBe('Doe');
});

it('maps nested data to flat data before export', function () {
    $dto = SimpleUserDTO::fromArray([
        'name' => [
            'first_name' => 'John',
            'last_name' => 'Doe',
        ],
        'email' => 'john.doe@example.com',
    ]);

    $user = $dto->toModel(User::class);

    expect($dto->name)
        ->toBeInstanceOf(SimpleNameDTO::class)
        ->and($dto->name->first_name)
        ->toBe('John')
        ->and($dto->name->last_name)
        ->toBe('Doe')
        ->and($dto->email)
        ->toBe('john.doe@example.com')
        ->and($user->first_name)
        ->toBe('John')
        ->and($user->last_name)
        ->toBe('Doe')
        ->and($user->email)
        ->toBe('john.doe@example.com');
});

it('casts properties with castable classes and callables', function () {
    $dto = CallableCastingDTOInstance::fromArray([
        'name' => [
            'first_name' => 'John',
            'last_name' => 'Doe',
        ],
        'age' => '30',
    ]);

    expect($dto->name)
        ->toBeInstanceOf(SimpleNameDTO::class)
        ->and($dto->name->first_name)
        ->toBe('John')
        ->and($dto->name->last_name)
        ->toBe('Doe')
        ->and($dto->age)
        ->toBe(30);
});

it('checks that update for property reflects while converting DTO', function () {
    $dto = AttributesDTO::fromArray([
        'age' => 18,
        'doc' => 'test',
    ]);

    $dto->age = 20;

    expect($dto->age)->toBe(20)
        ->and($dto->toArray())->toBe(['age' => 20, 'doc' => 'test']);
});
