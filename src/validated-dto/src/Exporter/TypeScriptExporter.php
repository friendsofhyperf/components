<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\ValidatedDTO\Exporter;

use Exception;
use FriendsOfHyperf\ValidatedDTO\Casting\ArrayCast;
use FriendsOfHyperf\ValidatedDTO\Casting\BooleanCast;
use FriendsOfHyperf\ValidatedDTO\Casting\CarbonCast;
use FriendsOfHyperf\ValidatedDTO\Casting\CarbonImmutableCast;
use FriendsOfHyperf\ValidatedDTO\Casting\CollectionCast;
use FriendsOfHyperf\ValidatedDTO\Casting\DTOCast;
use FriendsOfHyperf\ValidatedDTO\Casting\FloatCast;
use FriendsOfHyperf\ValidatedDTO\Casting\IntegerCast;
use FriendsOfHyperf\ValidatedDTO\Casting\ModelCast;
use FriendsOfHyperf\ValidatedDTO\Casting\ObjectCast;
use FriendsOfHyperf\ValidatedDTO\Casting\StringCast;
use ReflectionClass;
use ReflectionNamedType;
use ReflectionProperty;
use ReflectionUnionType;

/**
 * @property string $dtoClass
 */
class TypeScriptExporter extends AbstractExporter
{
    /**
     * Generate TypeScript interface from reflection.
     */
    protected function generate(ReflectionClass $reflection): string
    {
        $className = $reflection->getShortName();
        $properties = $this->getPublicProperties($reflection);
        $casts = $this->getCasts($reflection);

        $typescript = "export interface {$className} {\n";

        if (empty($properties)) {
            $typescript .= "  // No public properties found\n";
        }

        foreach ($properties as $property) {
            $propertyName = $property->getName();
            $tsType = $this->getTypescriptType($property, $casts[$propertyName] ?? null);
            $optional = $this->isOptionalProperty($property) ? '?' : '';

            $typescript .= "  {$propertyName}{$optional}: {$tsType};\n";
        }

        $typescript .= "}\n";

        return $typescript;
    }

    /**
     * Get TypeScript type for property.
     * @param null|mixed $cast
     */
    protected function getTypescriptType(ReflectionProperty $property, $cast = null): string
    {
        // If there's a cast, use it to determine the type
        if ($cast !== null) {
            return $this->mapCastToTypescript($cast);
        }

        // Fall back to PHP type hints
        $type = $property->getType();
        if ($type === null) {
            return 'any';
        }

        if ($type instanceof ReflectionUnionType) {
            $types = [];
            foreach ($type->getTypes() as $unionType) {
                if ($unionType->getName() === 'null') {
                    continue; // Skip null in union types, handle via optional property
                }
                $types[] = $this->mapPhpTypeToTypescript($unionType->getName());
            }
            return empty($types) ? 'any' : implode(' | ', array_unique($types));
        }

        if ($type instanceof ReflectionNamedType) {
            return $this->mapPhpTypeToTypescript($type->getName());
        }

        return 'any';
    }

    /**
     * Map cast to TypeScript type.
     * @param mixed $cast
     */
    protected function mapCastToTypescript($cast): string
    {
        return match (true) {
            $cast instanceof StringCast => 'string',
            $cast instanceof IntegerCast => 'number',
            $cast instanceof FloatCast => 'number',
            $cast instanceof BooleanCast => 'boolean',
            $cast instanceof ArrayCast => 'any[]',
            $cast instanceof CollectionCast => 'any[]',
            $cast instanceof CarbonCast => 'string', // ISO date string
            $cast instanceof CarbonImmutableCast => 'string', // ISO date string
            $cast instanceof DTOCast => $this->extractDTOTypeName($cast),
            $cast instanceof ModelCast => 'Record<string, any>',
            $cast instanceof ObjectCast => 'Record<string, any>',
            default => 'any',
        };
    }

    /**
     * Extract DTO type name from DTOCast.
     */
    protected function extractDTOTypeName(DTOCast $cast): string
    {
        try {
            /** @var string $dtoClass */
            $dtoClass = (fn () => $this->dtoClass)->call($cast);

            $parts = explode('\\', $dtoClass);
            return end($parts);
        } catch (Exception $e) {
            // Fall back to generic object
        }

        return 'Record<string, any>';
    }

    /**
     * Map PHP type to TypeScript type.
     */
    protected function mapPhpTypeToTypescript(string $phpType): string
    {
        return match ($phpType) {
            'string' => 'string',
            'int', 'integer', 'float', 'double' => 'number',
            'bool', 'boolean' => 'boolean',
            'array' => 'any[]',
            'object' => 'Record<string, any>',
            'mixed' => 'any',
            default => $this->isClassType($phpType) ? $this->getClassTypeName($phpType) : 'any',
        };
    }

    /**
     * Check if type is a class type.
     */
    protected function isClassType(string $type): bool
    {
        return class_exists($type) || interface_exists($type);
    }

    /**
     * Get class type name for TypeScript.
     */
    protected function getClassTypeName(string $type): string
    {
        $parts = explode('\\', $type);
        $className = end($parts);

        // For common framework classes, map to appropriate TS types
        if (str_contains($type, '\Carbon\Carbon')) {
            return 'string'; // ISO date string
        }

        if (str_contains($type, '\Hyperf\Collection\Collection')) {
            return 'any[]';
        }

        if (str_contains($type, 'DTO')) {
            return $className;
        }

        return 'Record<string, any>';
    }

    /**
     * Check if property is optional.
     */
    protected function isOptionalProperty(ReflectionProperty $property): bool
    {
        $type = $property->getType();
        if (! $type) {
            return false;
        }

        // Check for nullable types
        if ($type->allowsNull()) {
            return true;
        }

        // Check union types for null
        if ($type instanceof ReflectionUnionType) {
            foreach ($type->getTypes() as $unionType) {
                if ($unionType->getName() === 'null') {
                    return true;
                }
            }
        }

        return false;
    }
}
