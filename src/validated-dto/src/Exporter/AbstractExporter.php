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
use InvalidArgumentException;
use ReflectionClass;
use ReflectionProperty;

abstract class AbstractExporter implements ExporterInterface
{
    /**
     * Export a DTO class.
     */
    public function export(string $className): string
    {
        if (! class_exists($className)) {
            throw new InvalidArgumentException("Class {$className} does not exist.");
        }

        $reflection = new ReflectionClass($className);

        if (! $this->isDTOClass($reflection)) {
            throw new InvalidArgumentException("Class {$className} is not a DTO class.");
        }

        return $this->generate($reflection);
    }

    /**
     * Check if the class is a DTO class.
     */
    protected function isDTOClass(ReflectionClass $reflection): bool
    {
        static $dtoClasses = [
            \FriendsOfHyperf\ValidatedDTO\ValidatedDTO::class,
            \FriendsOfHyperf\ValidatedDTO\SimpleDTO::class,
        ];

        $parentClass = $reflection->getParentClass();
        while ($parentClass) {
            if (in_array($parentClass->getName(), $dtoClasses, true)) {
                return true;
            }
            $parentClass = $parentClass->getParentClass();
        }

        return false;
    }

    /**
     * Get public properties from reflection.
     */
    protected function getPublicProperties(ReflectionClass $reflection): array
    {
        return array_filter(
            $reflection->getProperties(ReflectionProperty::IS_PUBLIC),
            fn ($property) => ! $property->isStatic()
        );
    }

    /**
     * Get casts from DTO.
     */
    protected function getCasts(ReflectionClass $reflection): array
    {
        if (! $reflection->hasMethod('casts')) {
            return [];
        }

        try {
            $instance = $reflection->newInstanceWithoutConstructor();
            $castsMethod = $reflection->getMethod('casts');
            $castsMethod->setAccessible(true);
            $casts = $castsMethod->invoke($instance);
            return is_array($casts) ? $casts : [];
        } catch (Exception $e) {
            // If we can't get casts, continue without them
            return [];
        }
    }

    /**
     * Generate the export content from reflection.
     */
    abstract protected function generate(ReflectionClass $reflection): string;
}
