<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */
use FriendsOfHyperf\Tests\ValidatedDTO\Datasets\NameDTO;
use FriendsOfHyperf\Tests\ValidatedDTO\Datasets\SimpleNameDTO;
use FriendsOfHyperf\Tests\ValidatedDTO\Datasets\UserDTO;
use FriendsOfHyperf\ValidatedDTO\Exporter\TypeScriptExporter;

beforeEach(function () {
    $this->exporter = new TypeScriptExporter();
});

it('exports simple DTO to TypeScript interface', function () {
    $result = $this->exporter->export(SimpleNameDTO::class);

    expect($result)->toContain('export interface SimpleNameDTO');
    expect($result)->toContain('first_name: string;');
    expect($result)->toContain('last_name: string;');
});

it('exports ValidatedDTO to TypeScript interface', function () {
    $result = $this->exporter->export(NameDTO::class);

    expect($result)->toContain('export interface NameDTO');
    expect($result)->toContain('first_name: string;');
    expect($result)->toContain('last_name: string;');
});

it('exports nested DTO with proper type mapping', function () {
    $result = $this->exporter->export(UserDTO::class);

    expect($result)->toContain('export interface UserDTO');
    expect($result)->toContain('name: NameDTO;');
    expect($result)->toContain('email: string;');
});

it('throws exception for non-existent class', function () {
    expect(fn () => $this->exporter->export('NonExistentClass'))
        ->toThrow(InvalidArgumentException::class, 'Class NonExistentClass does not exist.');
});

it('throws exception for non-DTO class', function () {
    expect(fn () => $this->exporter->export(stdClass::class))
        ->toThrow(InvalidArgumentException::class, 'Class stdClass is not a DTO class.');
});

it('handles DTO with inherited public properties', function () {
    // Create a temporary DTO class for testing using an anonymous class
    $dto = new class extends \FriendsOfHyperf\ValidatedDTO\ValidatedDTO {
        protected function rules(): array { return []; }
        protected function defaults(): array { return []; }
        protected function casts(): array { return []; }
    };

    $result = $this->exporter->export(get_class($dto));

    expect($result)->toContain('export interface ' . (new \ReflectionClass($dto))->getShortName());
    expect($result)->toContain('lazyValidation: boolean;');
});

it('maps different cast types to correct TypeScript types', function () {
    // Create a DTO with various cast types for testing using an anonymous class
    $dto = new class extends \FriendsOfHyperf\ValidatedDTO\ValidatedDTO {
        public string $stringProp;
        public int $intProp;
        public bool $boolProp;
        public array $arrayProp;
        public \Carbon\Carbon $dateProp;

        protected function rules(): array { return []; }
        protected function defaults(): array { return []; }
        protected function casts(): array {
            return [
                "stringProp" => new \FriendsOfHyperf\ValidatedDTO\Casting\StringCast(),
                "intProp" => new \FriendsOfHyperf\ValidatedDTO\Casting\IntegerCast(),
                "boolProp" => new \FriendsOfHyperf\ValidatedDTO\Casting\BooleanCast(),
                "arrayProp" => new \FriendsOfHyperf\ValidatedDTO\Casting\ArrayCast(),
                "dateProp" => new \FriendsOfHyperf\ValidatedDTO\Casting\CarbonCast(),
            ];
        }
    };

    $result = $this->exporter->export(get_class($dto));

    expect($result)->toContain('stringProp: string;');
    expect($result)->toContain('intProp: number;');
    expect($result)->toContain('boolProp: boolean;');
    expect($result)->toContain('arrayProp: any[];');
    expect($result)->toContain('dateProp: string;');
});

it('handles nullable properties correctly', function () {
    // Create a DTO with nullable properties
    eval('
        namespace FriendsOfHyperf\ValidatedDTO;
        class NullableTestDTO extends ValidatedDTO {
            public string $required;
            public ?string $optional;
            
            protected function rules(): array { return []; }
            protected function defaults(): array { return []; }
            protected function casts(): array { return []; }
        }
    ');

    $result = $this->exporter->export('FriendsOfHyperf\ValidatedDTO\NullableTestDTO');

    expect($result)->toContain('required: string;');
    expect($result)->toContain('optional?: string;');
});
