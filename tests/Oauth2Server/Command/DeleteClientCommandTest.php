<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Tests\Oauth2Server\Command;

use FriendsOfHyperf\Oauth2\Server\Command\DeleteClientCommand;
use FriendsOfHyperf\Oauth2\Server\Manager\ClientManagerInterface;
use FriendsOfHyperf\Oauth2\Server\Model\ClientInterface;
use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\ConfigInterface;
use Mockery;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

uses()->group('oauth2');

beforeEach(function () {
    $this->clientManager = Mockery::mock(ClientManagerInterface::class);
    $this->client = Mockery::mock(ClientInterface::class);
    $this->command = new DeleteClientCommand($this->clientManager);
    $mockConfig = Mockery::mock(ConfigInterface::class);
    ApplicationContext::getContainer()->set(ConfigInterface::class, $mockConfig);
    $mockConfig->shouldReceive('get')->andReturn([]);
});

afterEach(function () {
    ApplicationContext::getContainer()->unbind(ConfigInterface::class);
    Mockery::close();
});

it('requires identifier argument', function () {
    $input = new ArrayInput([]);
    $output = new BufferedOutput();

    $this->expectExceptionMessage('Not enough arguments (missing: "identifier").');
    $this->command->run($input, $output);
});

it('successfully deletes client', function () {
    $identifier = 'test-client-id';
    $this->clientManager->shouldReceive('find')
        ->once()
        ->with($identifier)
        ->andReturn($this->client);
    $this->clientManager->shouldReceive('remove')
        ->once()
        ->with($this->client)
        ->andReturn(true);

    $input = new ArrayInput(['identifier' => $identifier, '--force' => true]);
    $output = new BufferedOutput();

    $exitCode = $this->command->run($input, $output);
    expect($exitCode)->toBe(0)
        ->and($output->fetch())->toContain('OAuth2 client deleted successfully.');
});

it('fails to delete nonexistent_client', function () {
    $identifier = 'nonexistent-client-id';
    $this->clientManager->shouldReceive('find')
        ->once()
        ->with($identifier)
        ->andReturn(null);

    $input = new ArrayInput(['identifier' => $identifier]);
    $output = new BufferedOutput();

    $exitCode = $this->command->run($input, $output);
    expect($exitCode)->toBe(1)
        ->and($output->fetch())->toContain('OAuth2 client identified as "nonexistent-client-id" does not exist.');
});

it('supports force deletion without confirmation', function () {
    $identifier = 'test-client-id';
    $this->clientManager->shouldReceive('remove')
        ->once()
        ->with($this->client)
        ->andReturn(true);
    $this->clientManager->shouldReceive('find')
        ->once()
        ->with($identifier)
        ->andReturn($this->client);

    $input = new ArrayInput([
        'identifier' => $identifier,
        '--force' => true, // 显式添加 force 选项
    ]);
    $output = new BufferedOutput();

    $exitCode = $this->command->run($input, $output);

    expect($exitCode)->toBe(0);
});
