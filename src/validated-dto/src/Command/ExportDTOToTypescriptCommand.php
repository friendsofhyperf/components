<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\ValidatedDTO\Command;

use FriendsOfHyperf\ValidatedDTO\Casting\ArrayCast;
use FriendsOfHyperf\ValidatedDTO\Casting\BooleanCast;
use FriendsOfHyperf\ValidatedDTO\Casting\CarbonCast;
use FriendsOfHyperf\ValidatedDTO\Casting\CarbonImmutableCast;
use FriendsOfHyperf\ValidatedDTO\Casting\CollectionCast;
use FriendsOfHyperf\ValidatedDTO\Casting\DTOCast;
use FriendsOfHyperf\ValidatedDTO\Casting\DoubleCast;
use FriendsOfHyperf\ValidatedDTO\Casting\FloatCast;
use FriendsOfHyperf\ValidatedDTO\Casting\IntegerCast;
use FriendsOfHyperf\ValidatedDTO\Casting\LongCast;
use FriendsOfHyperf\ValidatedDTO\Casting\ModelCast;
use FriendsOfHyperf\ValidatedDTO\Casting\ObjectCast;
use FriendsOfHyperf\ValidatedDTO\Casting\StringCast;
use Hyperf\Contract\ConfigInterface;
use ReflectionClass;
use ReflectionProperty;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ExportDTOToTypescriptCommand extends SymfonyCommand
{
    protected InputInterface $input;

    protected OutputInterface $output;

    public function __construct(protected ConfigInterface $config)
    {
        parent::__construct('export:dto-typescript');
    }

    public function configure()
    {
        foreach ($this->getArguments() as $argument) {
            $this->addArgument(...$argument);
        }

        foreach ($this->getOptions() as $option) {
            $this->addOption(...$option);
        }

        $this->setDescription('Export DTO classes to TypeScript interfaces.');
        $this->setAliases(['export:dto-ts']);
    }

    /**
     * Execute the console command.
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->input = $input;
        $this->output = $output;

        $class = $input->getArgument('class');
        $outputPath = $input->getOption('output');

        if (!class_exists($class)) {
            $output->writeln(sprintf('<fg=red>Class %s does not exist!</>', $class));
            return 1;
        }

        try {
            $reflection = new ReflectionClass($class);
            
            // Check if it's a DTO class
            if (!$this->isDTOClass($reflection)) {
                $output->writeln(sprintf('<fg=red>Class %s is not a DTO class!</>', $class));
                return 1;
            }

            $typescript = $this->generateTypescript($reflection);
            
            if ($outputPath) {
                $this->writeToFile($outputPath, $typescript);
                $output->writeln(sprintf('<info>TypeScript interface exported to %s</info>', $outputPath));
            } else {
                $output->writeln($typescript);
            }

            return 0;
        } catch (\Exception $e) {
            $output->writeln(sprintf('<error>Error: %s</error>', $e->getMessage()));
            return 1;
        }
    }

    protected function isDTOClass(ReflectionClass $reflection): bool
    {
        $parentClass = $reflection->getParentClass();
        if (!$parentClass) {
            return false;
        }

        $parentName = $parentClass->getName();
        return $parentName === 'FriendsOfHyperf\ValidatedDTO\ValidatedDTO' 
            || $parentName === 'FriendsOfHyperf\ValidatedDTO\SimpleDTO'
            || $this->isDTOClass($parentClass);
    }

    protected function generateTypescript(ReflectionClass $reflection): string
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

    protected function getPublicProperties(ReflectionClass $reflection): array
    {
        return array_filter(
            $reflection->getProperties(ReflectionProperty::IS_PUBLIC),
            fn($property) => !$property->isStatic()
        );
    }

    protected function getCasts(ReflectionClass $reflection): array
    {
        if (!$reflection->hasMethod('casts')) {
            return [];
        }

        try {
            $instance = $reflection->newInstanceWithoutConstructor();
            $castsMethod = $reflection->getMethod('casts');
            $castsMethod->setAccessible(true);
            $casts = $castsMethod->invoke($instance);
            return is_array($casts) ? $casts : [];
        } catch (\Exception $e) {
            // If we can't get casts, continue without them
            return [];
        }
    }

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

        if ($type instanceof \ReflectionUnionType) {
            $types = [];
            foreach ($type->getTypes() as $unionType) {
                if ($unionType->getName() === 'null') {
                    continue; // Skip null in union types, handle via optional property
                }
                $types[] = $this->mapPhpTypeToTypescript($unionType->getName());
            }
            return empty($types) ? 'any' : implode(' | ', array_unique($types));
        }

        if ($type instanceof \ReflectionNamedType) {
            return $this->mapPhpTypeToTypescript($type->getName());
        }

        return 'any';
    }

    protected function mapCastToTypescript($cast): string
    {
        return match (true) {
            $cast instanceof StringCast => 'string',
            $cast instanceof IntegerCast => 'number',
            $cast instanceof LongCast => 'number',
            $cast instanceof FloatCast => 'number',
            $cast instanceof DoubleCast => 'number',
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

    protected function extractDTOTypeName(DTOCast $cast): string
    {
        try {
            $reflection = new ReflectionClass($cast);
            $property = $reflection->getProperty('dtoClass');
            $dtoClass = $property->getValue($cast);
            
            if (is_string($dtoClass)) {
                $parts = explode('\\', $dtoClass);
                return end($parts);
            }
        } catch (\Exception $e) {
            // Fall back to generic object
        }

        return 'Record<string, any>';
    }

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

    protected function isClassType(string $type): bool
    {
        return class_exists($type) || interface_exists($type);
    }

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

    protected function isOptionalProperty(ReflectionProperty $property): bool
    {
        $type = $property->getType();
        if (!$type) {
            return false;
        }

        // Check for nullable types
        if ($type->allowsNull()) {
            return true;
        }

        // Check union types for null
        if ($type instanceof \ReflectionUnionType) {
            foreach ($type->getTypes() as $unionType) {
                if ($unionType->getName() === 'null') {
                    return true;
                }
            }
        }

        return false;
    }

    protected function writeToFile(string $path, string $content): void
    {
        $directory = dirname($path);
        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }

        if (file_exists($path) && !$this->input->getOption('force')) {
            $this->output->writeln(sprintf('<error>File %s already exists! Use --force to overwrite.</error>', $path));
            return;
        }

        file_put_contents($path, $content);
    }

    /**
     * Get the console command arguments.
     */
    protected function getArguments(): array
    {
        return [
            ['class', InputArgument::REQUIRED, 'The fully qualified class name of the DTO'],
        ];
    }

    /**
     * Get the console command options.
     */
    protected function getOptions(): array
    {
        return [
            ['output', 'o', InputOption::VALUE_OPTIONAL, 'Output file path for the TypeScript interface', null],
            ['force', 'f', InputOption::VALUE_NONE, 'Overwrite existing files'],
        ];
    }
}