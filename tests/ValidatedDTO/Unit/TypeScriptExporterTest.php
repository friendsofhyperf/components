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
    expect(fn() => $this->exporter->export('NonExistentClass'))
        ->toThrow(InvalidArgumentException::class, 'Class NonExistentClass does not exist.');
});

it('throws exception for non-DTO class', function () {
    expect(fn() => $this->exporter->export(\stdClass::class))
        ->toThrow(InvalidArgumentException::class, 'Class stdClass is not a DTO class.');
});

it('handles DTO with no public properties', function () {
    // Create a temporary DTO class for testing
    eval('
        namespace FriendsOfHyperf\ValidatedDTO;
        class EmptyTestDTO extends ValidatedDTO {
            protected function rules(): array { return []; }
            protected function defaults(): array { return []; }
            protected function casts(): array { return []; }
        }
    ');

    $result = $this->exporter->export('FriendsOfHyperf\ValidatedDTO\EmptyTestDTO');

    expect($result)->toContain('export interface EmptyTestDTO');
    expect($result)->toContain('// No public properties found');
});

it('maps different cast types to correct TypeScript types', function () {
    // Create a DTO with various cast types for testing
    eval('
        use FriendsOfHyperf\ValidatedDTO\Casting\StringCast;
        use FriendsOfHyperf\ValidatedDTO\Casting\IntegerCast;
        use FriendsOfHyperf\ValidatedDTO\Casting\BooleanCast;
        use FriendsOfHyperf\ValidatedDTO\Casting\ArrayCast;
        use FriendsOfHyperf\ValidatedDTO\Casting\CarbonCast;
        
        namespace FriendsOfHyperf\ValidatedDTO;
        class TypeTestDTO extends ValidatedDTO {
            public string $stringProp;
            public int $intProp;
            public bool $boolProp;
            public array $arrayProp;
            public \Carbon\Carbon $dateProp;
            
            protected function rules(): array { return []; }
            protected function defaults(): array { return []; }
            protected function casts(): array { 
                return [
                    "stringProp" => new StringCast(),
                    "intProp" => new IntegerCast(),
                    "boolProp" => new BooleanCast(),
                    "arrayProp" => new ArrayCast(),
                    "dateProp" => new CarbonCast(),
                ];
            }
        }
    ');

    $result = $this->exporter->export('FriendsOfHyperf\ValidatedDTO\TypeTestDTO');

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