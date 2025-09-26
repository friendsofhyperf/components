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
use FriendsOfHyperf\ValidatedDTO\Command\ExportDTOToTypescriptCommand;
use Hyperf\Contract\ConfigInterface;
use Mockery as m;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

beforeEach(function () {
    $this->config = m::mock(ConfigInterface::class);
    $this->command = new ExportDTOToTypescriptCommand($this->config);
});

afterEach(function () {
    m::close();
});

it('generates TypeScript interface for simple DTO', function () {
    $input = new ArrayInput(['class' => SimpleNameDTO::class]);
    $output = new BufferedOutput();

    $result = $this->command->execute($input, $output);

    expect($result)->toBe(0);
    
    $outputContent = $output->fetch();
    expect($outputContent)->toContain('export interface SimpleNameDTO');
    expect($outputContent)->toContain('first_name: string;');
    expect($outputContent)->toContain('last_name: string;');
});

it('generates TypeScript interface for ValidatedDTO', function () {
    $input = new ArrayInput(['class' => NameDTO::class]);
    $output = new BufferedOutput();

    $result = $this->command->execute($input, $output);

    expect($result)->toBe(0);
    
    $outputContent = $output->fetch();
    expect($outputContent)->toContain('export interface NameDTO');
    expect($outputContent)->toContain('first_name: string;');
    expect($outputContent)->toContain('last_name: string;');
});

it('generates TypeScript interface for nested DTO', function () {
    $input = new ArrayInput(['class' => UserDTO::class]);
    $output = new BufferedOutput();

    $result = $this->command->execute($input, $output);

    expect($result)->toBe(0);
    
    $outputContent = $output->fetch();
    expect($outputContent)->toContain('export interface UserDTO');
    expect($outputContent)->toContain('name: NameDTO;');
    expect($outputContent)->toContain('email: string;');
});

it('outputs to file when output option is provided', function () {
    $outputFile = '/tmp/test-dto.ts';
    
    // Clean up any existing file
    if (file_exists($outputFile)) {
        unlink($outputFile);
    }

    $input = new ArrayInput([
        'class' => SimpleNameDTO::class,
        '--output' => $outputFile
    ]);
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

it('returns error for non-existent class', function () {
    $input = new ArrayInput(['class' => 'NonExistentClass']);
    $output = new BufferedOutput();

    $result = $this->command->execute($input, $output);

    expect($result)->toBe(1);
    expect($output->fetch())->toContain('Class NonExistentClass does not exist!');
});

it('returns error for non-DTO class', function () {
    $input = new ArrayInput(['class' => \stdClass::class]);
    $output = new BufferedOutput();

    $result = $this->command->execute($input, $output);

    expect($result)->toBe(1);
    expect($output->fetch())->toContain('is not a DTO class!');
});