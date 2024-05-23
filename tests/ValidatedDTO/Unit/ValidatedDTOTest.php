<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use FriendsOfHyperf\Tests\ValidatedDTO\Datasets\DummyBackedEnum;
use FriendsOfHyperf\Tests\ValidatedDTO\Datasets\DummyEnum;
use FriendsOfHyperf\Tests\ValidatedDTO\Datasets\MapBeforeExportDTO;
use FriendsOfHyperf\Tests\ValidatedDTO\Datasets\MapBeforeValidationDTO;
use FriendsOfHyperf\Tests\ValidatedDTO\Datasets\MapDataDTO;
use FriendsOfHyperf\Tests\ValidatedDTO\Datasets\MappedNameDTO;
use FriendsOfHyperf\Tests\ValidatedDTO\Datasets\NameAfterDTO;
use FriendsOfHyperf\Tests\ValidatedDTO\Datasets\NameDTO;
use FriendsOfHyperf\Tests\ValidatedDTO\Datasets\NullableDTO;
use FriendsOfHyperf\Tests\ValidatedDTO\Datasets\User;
use FriendsOfHyperf\Tests\ValidatedDTO\Datasets\UserDTO;
use FriendsOfHyperf\Tests\ValidatedDTO\Datasets\UserNestedCollectionDTO;
use FriendsOfHyperf\Tests\ValidatedDTO\Datasets\UserNestedDTO;
use FriendsOfHyperf\Tests\ValidatedDTO\Datasets\ValidatedDTOInstance;
use FriendsOfHyperf\Tests\ValidatedDTO\Datasets\ValidatedEnumDTO;
use FriendsOfHyperf\Tests\ValidatedDTO\Datasets\ValidatedFileDTO;
use FriendsOfHyperf\ValidatedDTO\Exception\InvalidJsonException;
use FriendsOfHyperf\ValidatedDTO\ValidatedDTO;
use Hyperf\Command\Command;
use Hyperf\Database\Model\Model;
use Hyperf\HttpMessage\Upload\UploadedFile;
use Hyperf\Validation\ValidationException;
use Mockery as m;
use Psr\Http\Message\RequestInterface;
use Symfony\Component\Console\Input\InputInterface;

beforeEach(function () {
    $this->subject_name = faker()->name();
    $this->subject_email = faker()->unique()->safeEmail();
    // set timezone to UTC to avoid issues with Carbon
    date_default_timezone_set('UTC');
});

it('instantiates a ValidatedDTO validating its data', function () {
    $validatedDTO = new ValidatedDTOInstance(['name' => $this->subject_name]);

    expect($validatedDTO)->toBeInstanceOf(ValidatedDTO::class)
        ->and($validatedDTO->validatedData)
        ->toBe(['name' => $this->subject_name])
        ->and($validatedDTO->validator->passes())
        ->toBeTrue();
});
it('throws exception when trying to instantiate a ValidatedDTO with invalid data')
    ->expect(fn () => new ValidatedDTOInstance([]))
    ->throws(ValidationException::class);

it('instantiates a ValidatedDTO with nullable and optional properties', function () {
    $dto = new NullableDTO([
        'name' => $this->subject_name,
        'address' => null,
    ]);

    expect($dto)->toBeInstanceOf(NullableDTO::class)
        ->and($dto->name)
        ->toBeString()
        ->and($dto->age)
        ->toBeNull()
        ->and($dto->address)
        ->toBeNull();
});

it('handles the after hook when instantiating a ValidatedDTO')
    ->expect(fn () => new NameAfterDTO([
        'first_name' => $this->subject_name,
        'last_name' => $this->subject_name,
    ]))
    ->throws(ValidationException::class);

it('returns null when trying to access a property that does not exist', function () {
    $validatedDTO = new ValidatedDTOInstance(['name' => $this->subject_name]);

    expect($validatedDTO->age)->toBeNull();
});

it('validates that is possible to set a property in a ValidatedDTO', function () {
    $validatedDTO = new ValidatedDTOInstance(['name' => $this->subject_name]);

    $validatedDTO->age = 30;

    expect($validatedDTO->age)->toBe(30);
});

it('validates that a ValidatedDTO can be instantiated from a JSON string', function () {
    $validatedDTO = ValidatedDTOInstance::fromJson('{"name": "' . $this->subject_name . '"}');

    expect($validatedDTO->validatedData)
        ->ToBe(['name' => $this->subject_name])
        ->and($validatedDTO->validator->passes())
        ->toBeTrue();
});

it('throws exception when trying to instantiate a ValidatedDTO from an invalid JSON string')
    ->expect(fn () => ValidatedDTOInstance::fromJson('{"name": "' . $this->subject_name . '"'))
    ->throws(InvalidJsonException::class);

it('validates that a ValidatedDTO can be instantiated from Array', function () {
    $validatedDTO = ValidatedDTOInstance::fromArray(['name' => $this->subject_name]);

    expect($validatedDTO->validatedData)
        ->toBe(['name' => $this->subject_name])
        ->and($validatedDTO->validator->passes())
        ->toBeTrue();
});

it('validates that a ValidatedDTO can be instantiated from a Request', function () {
    /** @var RequestInterface $request */
    $request = m::mock(RequestInterface::class, [
        'all' => ['name' => $this->subject_name],
    ]);

    $validatedDTO = ValidatedDTOInstance::fromRequest($request);

    expect($validatedDTO->validatedData)
        ->toBe(['name' => $this->subject_name])
        ->and($validatedDTO->validator->passes())
        ->toBeTrue();
});

it('validates that a ValidatedDTO can be instantiated from an Eloquent Model', function () {
    $model = new class() extends Model {
        protected array $fillable = ['name'];
    };

    $model->fill(['name' => $this->subject_name]);

    $validatedDTO = ValidatedDTOInstance::fromModel($model);

    expect($validatedDTO->validatedData)
        ->toBe(['name' => $this->subject_name])
        ->and($validatedDTO->validator->passes())
        ->toBeTrue();
});

it('validates that a ValidatedDTO can be instantiated from Command arguments', function () {
    $command = new class($this->subject_name) extends Command {
        public function __construct(protected string $subject_name)
        {
            $this->input = m::mock(InputInterface::class, [
                'getArguments' => ['name' => $this->subject_name],
            ]);
            parent::__construct();
        }
    };

    $validatedDTO = ValidatedDTOInstance::fromCommandArguments($command);

    expect($validatedDTO->validatedData)
        ->ToBe(['name' => $this->subject_name])
        ->and($validatedDTO->validator->passes())
        ->toBeTrue();
});

it('validates that a ValidatedDTO can be instantiated from Command options', function () {
    $command = new class($this->subject_name) extends Command {
        public function __construct(protected string $subject_name)
        {
            $this->input = m::mock(InputInterface::class, [
                'getOptions' => ['name' => $this->subject_name],
            ]);
            parent::__construct();
        }
    };

    $validatedDTO = ValidatedDTOInstance::fromCommandOptions($command);

    expect($validatedDTO->validatedData)
        ->toEqual(['name' => $this->subject_name])
        ->and($validatedDTO->validator->passes())
        ->toBeTrue();
});

it('validates that a ValidatedDTO can be instantiated from a Command', function () {
    $command = new class($this->subject_name) extends Command {
        public function __construct(protected string $subject_name)
        {
            $this->input = m::mock(InputInterface::class, [
                'getArguments' => ['name' => $this->subject_name],
                'getOptions' => ['age' => 30],
            ]);
            parent::__construct();
        }
    };

    $validatedDTO = ValidatedDTOInstance::fromCommand($command);

    expect($validatedDTO->validatedData)
        ->toEqual(['name' => $this->subject_name, 'age' => 30])
        ->and($validatedDTO->validator->passes())
        ->toBeTrue();
});

it('validates that the ValidatedDTO can be converted into an array', function () {
    $dataStructure = ['name' => $this->subject_name];
    $validatedDTO = new ValidatedDTOInstance($dataStructure);

    expect($validatedDTO)->toArray()
        ->toBe($dataStructure);
});

it('validates that the ValidatedDTO can be converted into a JSON string', function () {
    $dataStructure = ['name' => $this->subject_name];
    $validatedDTO = new ValidatedDTOInstance($dataStructure);

    expect($validatedDTO)->toJson()
        ->toBe(json_encode($dataStructure));
});

it('validates that the ValidatedDTO can be converted into a pretty JSON string', function () {
    $dataStructure = ['name' => $this->subject_name];
    $validatedDTO = new ValidatedDTOInstance($dataStructure);

    expect($validatedDTO)->toPrettyJson()
        ->toBe(json_encode($dataStructure, JSON_PRETTY_PRINT));
});

it('validates that the ValidatedDTO with nested data can be converted into an array', function () {
    $dataStructure = [
        'name' => [
            'first_name' => $this->subject_name,
            'last_name' => 'Doe',
        ],
        'email' => $this->subject_email,
    ];
    $validatedDTO = new UserNestedDTO($dataStructure);

    expect($validatedDTO)->toArray()
        ->toBe($dataStructure);
});

it('validates that the ValidatedDTO with nested data can be converted into a JSON string', function () {
    $dataStructure = [
        'name' => [
            'first_name' => $this->subject_name,
            'last_name' => 'Doe',
        ],
        'email' => $this->subject_email,
    ];
    $validatedDTO = new UserNestedDTO($dataStructure);

    expect($validatedDTO)->toJson()
        ->toBe(json_encode($dataStructure));
});

it('validates that the ValidatedDTO with nested data can be converted into a pretty JSON string', function () {
    $dataStructure = [
        'name' => [
            'first_name' => $this->subject_name,
            'last_name' => 'Doe',
        ],
        'email' => $this->subject_email,
    ];
    $validatedDTO = new UserNestedDTO($dataStructure);

    expect($validatedDTO)->toPrettyJson()
        ->toBe(json_encode($dataStructure, JSON_PRETTY_PRINT));
});

it('validates that the ValidatedDTO with nested collection data can be converted into an array', function () {
    $dataStructure = [
        'names' => [
            [
                'first_name' => $this->subject_name,
                'last_name' => 'Doe',
            ],
            [
                'first_name' => 'Jane',
                'last_name' => 'Doe',
            ],
        ],
        'email' => $this->subject_email,
    ];
    $validatedDTO = new UserNestedCollectionDTO($dataStructure);

    expect($validatedDTO)->toArray()
        ->toBe($dataStructure);
});

it('validates that the ValidatedDTO with nested collection data can be converted into a JSON string', function () {
    $dataStructure = [
        'names' => [
            [
                'first_name' => $this->subject_name,
                'last_name' => 'Doe',
            ],
            [
                'first_name' => 'Jane',
                'last_name' => 'Doe',
            ],
        ],
        'email' => $this->subject_email,
    ];
    $validatedDTO = new UserNestedCollectionDTO($dataStructure);

    expect($validatedDTO)->toJson()
        ->toBe(json_encode($dataStructure));
});

it('validates that the ValidatedDTO with nested collection data can be converted into a pretty JSON string', function () {
    $dataStructure = [
        'names' => [
            [
                'first_name' => $this->subject_name,
                'last_name' => 'Doe',
            ],
            [
                'first_name' => 'Jane',
                'last_name' => 'Doe',
            ],
        ],
        'email' => $this->subject_email,
    ];
    $validatedDTO = new UserNestedCollectionDTO($dataStructure);

    expect($validatedDTO)->toPrettyJson()
        ->toBe(json_encode($dataStructure, JSON_PRETTY_PRINT));
});

it('validates that the ValidatedDTO with Enums and Carbon properties can be correctly converted into an array', function () {
    $dto = new ValidatedEnumDTO([]);

    expect($dto)->toBeInstanceOf(ValidatedEnumDTO::class)
        ->and($dto->unitEnum)
        ->toBeInstanceOf(DummyEnum::class)
        ->and($dto->backedEnum)
        ->toBeInstanceOf(DummyBackedEnum::class)
        ->and($dto->carbon)
        ->toBeInstanceOf(Carbon::class)
        ->and($dto->carbonImmutable)
        ->toBeInstanceOf(CarbonImmutable::class)
        ->and($dto->toArray())
        ->toBe([
            'unitEnum' => 'ONE',
            'backedEnum' => 'bar',
            'carbon' => '2023-10-16T00:00:00.000000Z',
            'carbonImmutable' => '2023-10-15T00:00:00.000000Z',
        ]);
});

it('validates that the ValidatedDTO can be converted into an Eloquent Model', function () {
    $validatedDTO = new ValidatedDTOInstance(['name' => $this->subject_name]);

    $model = new class() extends Model {
        protected array $fillable = ['name'];
    };

    $model_instance = $validatedDTO->toModel($model::class);

    expect($model_instance)
        ->toBeInstanceOf(Model::class)
        ->toArray()
        ->toBe(['name' => $this->subject_name]);
});

it('maps data before validation', function () {
    $dto = MapBeforeValidationDTO::fromArray(['full_name' => $this->subject_name]);

    expect($dto->full_name)
        ->toBeNull()
        ->and($dto->name)
        ->toBe($this->subject_name);
});

it('maps data before export', function () {
    $dto = MapBeforeExportDTO::fromArray(['name' => $this->subject_name]);

    expect($dto->name)
        ->toBe($this->subject_name)
        ->and($dto->username)
        ->toBeNull()
        ->and($dto->toArray())
        ->toBe(['username' => $this->subject_name]);
});

it('maps data before validation and before export', function () {
    $dto = MapDataDTO::fromArray(['full_name' => $this->subject_name]);

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
    $dto = MappedNameDTO::fromArray([
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
    $dto = UserDTO::fromArray([
        'name' => [
            'first_name' => 'John',
            'last_name' => 'Doe',
        ],
        'email' => 'john.doe@example.com',
    ]);

    $user = $dto->toModel(User::class);

    expect($dto->name)
        ->toBeInstanceOf(NameDTO::class)
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

it('validates that ValidatedDTO can be instantiated with file validation rules', function () {
    $uploadedFile = m::mock(UploadedFile::class, [
        'clientFilename' => 'avatar.jpg',
        'clientMediaType' => 'image/jpeg',
    ]);
    $validatedDTO = ValidatedFileDTO::fromArray(['file' => $uploadedFile]);

    expect($validatedDTO->validator->passes())
        ->toBeTrue();
});

it('validates that ValidateDTO cannot be instantiated with wrong mime type')
    ->expect(function () {
        $uploadedFile = m::mock(UploadedFile::class, [
            'clientFilename' => 'document.pdf',
            'clientMediaType' => 'application/pdf',
        ]);
        ValidatedFileDTO::fromArray(['file' => $uploadedFile]);
    })->throws(ValidationException::class);
