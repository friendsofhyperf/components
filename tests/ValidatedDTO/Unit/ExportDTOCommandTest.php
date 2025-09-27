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
use FriendsOfHyperf\ValidatedDTO\Command\ExportDTOCommand;
use FriendsOfHyperf\ValidatedDTO\Exporter\TypeScriptExporter;
use Mockery as m;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

beforeEach(function () {
    $this->container = m::mock(ContainerInterface::class);
    $this->exporter = m::mock(TypeScriptExporter::class);

    // Configure container to return the mocked exporter
    $this->container->shouldReceive('get')
        ->with(TypeScriptExporter::class)
        ->andReturn($this->exporter);

    $this->command = new ExportDTOCommand($this->container);
});

afterEach(function () {
    m::close();
});

it('generates TypeScript interface for simple DTO', function () {
    $this->exporter->shouldReceive('export')
        ->with(SimpleNameDTO::class)
        ->once()
        ->andReturn("export interface SimpleNameDTO {\n  first_name: string;\n  last_name: string;\n}\n");

    $input = new ArrayInput(['class' => SimpleNameDTO::class], $this->command->getDefinition());
    $output = new BufferedOutput();

    $result = $this->command->execute($input, $output);

    expect($result)->toBe(0);

    $outputContent = $output->fetch();
    expect($outputContent)->toContain('export interface SimpleNameDTO');
    expect($outputContent)->toContain('first_name: string;');
    expect($outputContent)->toContain('last_name: string;');
});

it('generates TypeScript interface for ValidatedDTO', function () {
    $this->exporter->shouldReceive('export')
        ->with(NameDTO::class)
        ->once()
        ->andReturn("export interface NameDTO {\n  first_name: string;\n  last_name: string;\n}\n");

    $input = new ArrayInput(['class' => NameDTO::class], $this->command->getDefinition());
    $output = new BufferedOutput();

    $result = $this->command->execute($input, $output);

    expect($result)->toBe(0);

    $outputContent = $output->fetch();
    expect($outputContent)->toContain('export interface NameDTO');
    expect($outputContent)->toContain('first_name: string;');
    expect($outputContent)->toContain('last_name: string;');
});

it('generates TypeScript interface for nested DTO', function () {
    $this->exporter->shouldReceive('export')
        ->with(UserDTO::class)
        ->once()
        ->andReturn("export interface UserDTO {\n  name: NameDTO;\n  email: string;\n}\n");

    $input = new ArrayInput(['class' => UserDTO::class], $this->command->getDefinition());
    $output = new BufferedOutput();

    $result = $this->command->execute($input, $output);

    expect($result)->toBe(0);

    $outputContent = $output->fetch();
    expect($outputContent)->toContain('export interface UserDTO');
    expect($outputContent)->toContain('name: NameDTO;');
    expect($outputContent)->toContain('email: string;');
});

it('outputs to file when output option is provided', function () {
    $outputFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'test-dto.ts';

    // Clean up any existing file
    if (file_exists($outputFile)) {
        unlink($outputFile);
    }

    $this->exporter->shouldReceive('export')
        ->with(SimpleNameDTO::class)
        ->once()
        ->andReturn("export interface SimpleNameDTO {\n  first_name: string;\n  last_name: string;\n}\n");

    $input = new ArrayInput([
        'class' => SimpleNameDTO::class,
        '--output' => $outputFile,
    ], $this->command->getDefinition());
    $output = new BufferedOutput();

    $result = $this->command->execute($input, $output);

    expect($result)->toBe(0);
    expect(file_exists($outputFile))->toBeTrue();

    $fileContent = file_get_contents($outputFile);
    expect($fileContent)->toContain('export interface SimpleNameDTO');
    expect($fileContent)->toContain('first_name: string;');
    expect($fileContent)->toContain('last_name: string;');

    // Clean up
    unlink($outputFile);
});

it('creates directory when output path does not exist', function () {
    $outputDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'test-dto-dir';
    $outputFile = $outputDir . DIRECTORY_SEPARATOR . 'test-dto.ts';

    // Clean up any existing directory
    if (is_dir($outputDir)) {
        if (file_exists($outputFile)) {
            unlink($outputFile);
        }
        rmdir($outputDir);
    }

    $this->exporter->shouldReceive('export')
        ->with(SimpleNameDTO::class)
        ->once()
        ->andReturn("export interface SimpleNameDTO {\n  first_name: string;\n  last_name: string;\n}\n");

    $input = new ArrayInput([
        'class' => SimpleNameDTO::class,
        '--output' => $outputFile,
    ], $this->command->getDefinition());
    $output = new BufferedOutput();

    $result = $this->command->execute($input, $output);

    expect($result)->toBe(0);
    expect(is_dir($outputDir))->toBeTrue();
    expect(file_exists($outputFile))->toBeTrue();

    // Clean up
    unlink($outputFile);
    rmdir($outputDir);
});

it('returns error for non-existent class', function () {
    $this->exporter->shouldReceive('export')
        ->with('NonExistentClass')
        ->once()
        ->andThrow(new InvalidArgumentException('Class NonExistentClass does not exist.'));

    $input = new ArrayInput(['class' => 'NonExistentClass'], $this->command->getDefinition());
    $output = new BufferedOutput();

    $result = $this->command->execute($input, $output);

    expect($result)->toBe(1);
    expect($output->fetch())->toContain('Class NonExistentClass does not exist.');
});

it('returns error for non-DTO class', function () {
    $this->exporter->shouldReceive('export')
        ->with(stdClass::class)
        ->once()
        ->andThrow(new InvalidArgumentException('Class stdClass is not a DTO class.'));

    $input = new ArrayInput(['class' => stdClass::class], $this->command->getDefinition());
    $output = new BufferedOutput();

    $result = $this->command->execute($input, $output);

    expect($result)->toBe(1);
    expect($output->fetch())->toContain('Class stdClass is not a DTO class.');
});

it('has correct command name and aliases', function () {
    expect($this->command->getName())->toBe('dto:export');
    expect($this->command->getAliases())->toBe([]);
});

it('has correct description', function () {
    expect($this->command->getDescription())->toBe('Export DTO classes to various formats.');
});

it('supports typescript language option', function () {
    $this->exporter->shouldReceive('export')
        ->with(SimpleNameDTO::class)
        ->once()
        ->andReturn("export interface SimpleNameDTO {\n  first_name: string;\n  last_name: string;\n}\n");

    $input = new ArrayInput([
        'class' => SimpleNameDTO::class,
        '--lang' => 'typescript',
    ], $this->command->getDefinition());
    $output = new BufferedOutput();

    $result = $this->command->execute($input, $output);

    expect($result)->toBe(0);

    $outputContent = $output->fetch();
    expect($outputContent)->toContain('export interface SimpleNameDTO');
});

it('defaults to typescript language', function () {
    $this->exporter->shouldReceive('export')
        ->with(SimpleNameDTO::class)
        ->once()
        ->andReturn("export interface SimpleNameDTO {\n  first_name: string;\n  last_name: string;\n}\n");

    $input = new ArrayInput(['class' => SimpleNameDTO::class], $this->command->getDefinition());
    $output = new BufferedOutput();

    $result = $this->command->execute($input, $output);

    expect($result)->toBe(0);
});

it('returns error for unsupported language', function () {
    $input = new ArrayInput([
        'class' => SimpleNameDTO::class,
        '--lang' => 'unsupported',
    ], $this->command->getDefinition());
    $output = new BufferedOutput();

    $result = $this->command->execute($input, $output);

    expect($result)->toBe(1);
    expect($output->fetch())->toContain('Unsupported language: unsupported');
});
