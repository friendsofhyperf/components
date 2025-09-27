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
use Throwable;

/**
 * @property string $dtoClass
 */
class TypeScriptExporter extends AbstractExporter
{
    public const TS_STRING = 'string';

    public const TS_NUMBER = 'number';

    public const TS_BOOLEAN = 'boolean';

    public const TS_ARRAY = 'any[]';

    public const TS_RECORD = 'Record<string, any>';

    public const TS_ANY = 'any';

    public const INTERFACE_TEMPLATE = "export interface %s {\n%s}\n";

    public const NO_PROPERTIES_COMMENT = "  // No public properties found\n";

    /**
     * Generate TypeScript interface from reflection.
     */
    protected function generate(ReflectionClass $reflection): string
    {
        $className = $reflection->getShortName();
        $properties = $this->getPublicProperties($reflection);
        $casts = $this->getCasts($reflection);

        $propertiesContent = '';

        if (empty($properties)) {
            $propertiesContent = self::NO_PROPERTIES_COMMENT;
        } else {
            foreach ($properties as $property) {
                $propertyName = $property->getName();
                $tsType = $this->getTypescriptType($property, $casts[$propertyName] ?? null);
                $optional = $this->isOptionalProperty($property) ? '?' : '';

                $propertiesContent .= "  {$propertyName}{$optional}: {$tsType};\n";
            }
        }

        return sprintf(self::INTERFACE_TEMPLATE, $className, $propertiesContent);
    }

    /**
     * Get TypeScript type for property.
     */
    protected function getTypescriptType(ReflectionProperty $property, mixed $cast = null): string
    {
        // If there's a cast, use it to determine the type
        if ($cast !== null) {
            return $this->mapCastToTypescript($cast);
        }

        // Fall back to PHP type hints
        $type = $property->getType();

        if ($type === null) {
            return self::TS_ANY;
        }

        if ($type instanceof ReflectionUnionType) {
            $types = [];
            foreach ($type->getTypes() as $unionType) {
                if ($unionType->getName() === 'null') {
                    continue; // Skip null in union types, handle via optional property
                }
                $types[] = $this->mapPhpTypeToTypescript($unionType->getName());
            }
            return empty($types) ? self::TS_ANY : implode(' | ', array_unique($types));
        }

        if ($type instanceof ReflectionNamedType) {
            return $this->mapPhpTypeToTypescript($type->getName());
        }

        return self::TS_ANY;
    }

    /**
     * Map cast to TypeScript type.
     */
    protected function mapCastToTypescript(mixed $cast): string
    {
        return match (true) {
            $cast instanceof StringCast => self::TS_STRING,
            $cast instanceof IntegerCast => self::TS_NUMBER,
            $cast instanceof FloatCast => self::TS_NUMBER,
            $cast instanceof BooleanCast => self::TS_BOOLEAN,
            $cast instanceof ArrayCast => self::TS_ARRAY,
            $cast instanceof CollectionCast => self::TS_ARRAY,
            $cast instanceof CarbonCast => self::TS_STRING, // ISO date string
            $cast instanceof CarbonImmutableCast => self::TS_STRING, // ISO date string
            $cast instanceof DTOCast => $this->extractDTOTypeName($cast),
            $cast instanceof ModelCast => self::TS_RECORD,
            $cast instanceof ObjectCast => self::TS_RECORD,
            default => self::TS_ANY,
        };
    }

    /**
     * Extract DTO type name from DTOCast.
     */
    protected function extractDTOTypeName(DTOCast $cast): string
    {
        try {
            /** @var string $dtoClass */
            $dtoClass = (fn () => $this->dtoClass ?? '')->call($cast);

            if ($dtoClass && class_exists($dtoClass)) {
                $parts = explode('\\', $dtoClass);
                return end($parts);
            }
        } catch (Throwable) {
            // Property doesn't exist
        }

        return self::TS_RECORD;
    }

    /**
     * Map PHP type to TypeScript type.
     */
    protected function mapPhpTypeToTypescript(string $phpType): string
    {
        return match ($phpType) {
            'string' => self::TS_STRING,
            'int', 'integer', 'float', 'double' => self::TS_NUMBER,
            'bool', 'boolean' => self::TS_BOOLEAN,
            'array' => self::TS_ARRAY,
            'object' => self::TS_RECORD,
            'mixed' => self::TS_ANY,
            default => $this->isClassType($phpType) ? $this->getClassTypeName($phpType) : self::TS_ANY,
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

        return match (true) {
            // For common framework classes, map to appropriate TS types
            str_contains($type, '\Carbon\Carbon') => self::TS_STRING, // ISO date string
            str_contains($type, '\Hyperf\Collection\Collection') => self::TS_ARRAY,
            str_contains($type, 'DTO') => $className,
            default => self::TS_RECORD,
        };
    }

    /**
     * Check if property is optional.
     */
    protected function isOptionalProperty(ReflectionProperty $property): bool
    {
        if (! $type = $property->getType()) {
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
