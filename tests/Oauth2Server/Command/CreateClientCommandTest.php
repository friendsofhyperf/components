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

use FriendsOfHyperf\Oauth2\Server\Command\CreateClientCommand;
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
    $configMock = $this->createMock(ConfigInterface::class);
    ApplicationContext::getContainer()->set(ConfigInterface::class, $configMock);
    $configMock->method('get')->willReturn([]);

    $this->command = new CreateClientCommand($this->clientManager, $this->client);
});

afterEach(function () {
    ApplicationContext::getContainer()->unbind(ConfigInterface::class);
    Mockery::close();
});

it('requires name argument', function () {
    $input = new ArrayInput([]);
    $output = new BufferedOutput();

    $this->expectExceptionMessage('Not enough arguments (missing: "name").');
    $this->command->run($input, $output);
});

it('creates client with minimal parameters', function () {
    $this->clientManager->shouldReceive('save')->once();
    $this->client->shouldReceive('newClientInstance')
        ->once()
        ->andReturnSelf();
    $this->client->shouldReceive('setActive')
        ->once()
        ->andReturnSelf();
    $this->client->shouldReceive('setAllowPlainTextPkce')
        ->once()
        ->andReturnSelf();
    $this->client->shouldReceive('setRedirectUris')
        ->once()
        ->andReturnSelf();
    $this->client->shouldReceive('setGrants')
        ->once()
        ->andReturnSelf();
    $this->client->shouldReceive('setScopes')
        ->once()
        ->andReturnSelf();
    $this->client->shouldReceive('getIdentifier')
        ->once()
        ->andReturn('custom-id');
    $this->client->shouldReceive('getSecret')
        ->once()->andReturn(null);
    $input = new ArrayInput(['name' => 'test-client']);
    $output = new BufferedOutput();

    $exitCode = $this->command->run($input, $output);
    expect($exitCode)->toBe(0);
});

it('generates identifier when not provided', function () {
    $this->client->shouldReceive('newClientInstance')
        ->andReturnUsing(function ($name, $identifier, $secret) {
            expect($secret)->not->toBeEmpty();
            return $this->client;
        });

    $input = new ArrayInput(['name' => 'test-client']);
    $output = new BufferedOutput();

    $this->command->run($input, $output);
});

it('uses provided identifier', function () {
    $identifier = 'custom-id';
    $this->client->shouldReceive('newClientInstance')
        ->andReturnUsing(function ($name, $argIdentifier) use ($identifier) {
            expect($argIdentifier)->toBe($identifier);
            return $this->client;
        });

    $input = new ArrayInput(['name' => 'test-client', 'identifier' => $identifier]);
    $output = new BufferedOutput();

    $this->command->run($input, $output);
});

it('handles public client with no secret', function () {
    $this->clientManager->shouldReceive('save')->once();
    $this->client->shouldReceive('newClientInstance')
        ->andReturnUsing(
            function (string $name, string $identifier, ?string $secret): ClientInterface {
                expect($name)->toBe('test-client')
                    ->and($secret)->toBeNull()
                    ->and($identifier)->not->toBeEmpty();
                return $this->client;
            }
        )->once();
    $this->client->shouldReceive('setActive')
        ->once()
        ->andReturnSelf();
    $this->client->shouldReceive('setAllowPlainTextPkce')
        ->once()
        ->andReturnSelf();
    $this->client->shouldReceive('setRedirectUris')
        ->once()
        ->andReturnSelf();
    $this->client->shouldReceive('setGrants')
        ->once()
        ->andReturnSelf();
    $this->client->shouldReceive('setScopes')
        ->once()
        ->andReturnSelf();
    $this->client->shouldReceive('getIdentifier')
        ->once()
        ->andReturn('custom-id');
    $this->client->shouldReceive('getSecret')
        ->once()->andReturn(null);

    $input = new ArrayInput([
        'name' => 'test-client',
        '--public' => true,
    ]);
    $output = new BufferedOutput();
    $exitCode = $this->command->run($input, $output);
    expect($exitCode)->toBe(0)
        ->and($output->fetch())->toContain('New OAuth2 client created successfully.');
});

it('throws exception for public client with secret', function () {
    $input = new ArrayInput([
        'name' => 'test-client',
        'secret' => 'invalid-secret',
        '--public' => true,
    ]);
    $output = new BufferedOutput();

    $exitCode = $this->command->run($input, $output);
    expect($exitCode)->toBe(1)
        ->and($output->fetch())
        ->toContain('The client cannot have a secret and be public.');
});

it('sets redirect uris correctly', function () {
    $this->clientManager->shouldReceive('save')->once();
    $redirectUris = ['https://example.com/callback1', 'https://example.com/callback2'];
    $this->client->shouldReceive('newClientInstance')
        ->andReturnUsing()->once()->andReturnSelf();
    $this->client->shouldReceive('setActive')
        ->once()
        ->andReturnSelf();
    $this->client->shouldReceive('setAllowPlainTextPkce')
        ->once()
        ->andReturnSelf();
    $this->client->shouldReceive('setRedirectUris')
        ->once()
        ->andReturnUsing(function (...$args) use ($redirectUris) {
            expect($args)->toHaveCount(count($redirectUris));
            foreach ($args as $arg) {
                expect((string) $arg)->toBeIn($redirectUris);
            }
            return $this->client;
        });
    $this->client->shouldReceive('setGrants')
        ->once()
        ->andReturnSelf();
    $this->client->shouldReceive('setScopes')
        ->once()
        ->andReturnSelf();
    $this->client->shouldReceive('getIdentifier')
        ->once()
        ->andReturn('custom-id');
    $this->client->shouldReceive('getSecret')
        ->once()->andReturn(null);

    $input = new ArrayInput([
        'name' => 'test-client',
        '--redirect-uri' => $redirectUris,
    ]);
    $output = new BufferedOutput();

    $statusCode = $this->command->run($input, $output);
    expect($statusCode)->toBe(0);
});

it('sets grants correctly', function () {
    $grants = ['authorization_code', 'client_credentials'];
    $this->clientManager->shouldReceive('save')->once();
    $this->client->shouldReceive('newClientInstance')
        ->andReturnUsing()->once()->andReturnSelf();
    $this->client->shouldReceive('setActive')
        ->once()
        ->andReturnSelf();
    $this->client->shouldReceive('setAllowPlainTextPkce')
        ->once()
        ->andReturnSelf();
    $this->client->shouldReceive('setRedirectUris')
        ->once()
        ->andReturnSelf();
    $this->client->shouldReceive('setGrants')
        ->once()
        ->andReturnUsing(function (...$args) use ($grants) {
            expect($args)->toHaveCount(count($grants));
            foreach ($args as $arg) {
                expect((string) $arg)->toBeIn($grants);
            }
            return $this->client;
        });
    $this->client->shouldReceive('setScopes')
        ->once()
        ->andReturnSelf();
    $this->client->shouldReceive('getIdentifier')
        ->once()
        ->andReturn('custom-id');
    $this->client->shouldReceive('getSecret')
        ->once()->andReturn(null);

    $input = new ArrayInput([
        'name' => 'test-client',
        '--grant-type' => $grants,
    ]);
    $output = new BufferedOutput();

    $this->command->run($input, $output);
});

it('sets scopes correctly', function () {
    $scopes = ['read', 'write'];
    $this->clientManager->shouldReceive('save')->once();
    $this->client->shouldReceive('newClientInstance')
        ->andReturnUsing()->once()->andReturnSelf();
    $this->client->shouldReceive('setActive')
        ->once()
        ->andReturnSelf();
    $this->client->shouldReceive('setAllowPlainTextPkce')
        ->once()
        ->andReturnSelf();
    $this->client->shouldReceive('setRedirectUris')
        ->once()
        ->andReturnSelf();
    $this->client->shouldReceive('setGrants')
        ->once()
        ->andReturnSelf();
    $this->client->shouldReceive('setScopes')
        ->once()
        ->andReturnUsing(function (...$args) use ($scopes) {
            expect($args)->toHaveCount(count($scopes));
            foreach ($args as $arg) {
                expect((string) $arg)->toBeIn($scopes);
            }
            return $this->client;
        });
    $this->client->shouldReceive('getIdentifier')
        ->once()
        ->andReturn('custom-id');
    $this->client->shouldReceive('getSecret')
        ->once()->andReturn(null);

    $input = new ArrayInput([
        'name' => 'test-client',
        '--scope' => $scopes,
    ]);
    $output = new BufferedOutput();

    $this->command->run($input, $output);
});

it('enables plain text pkce when option is set', function () {
    $this->clientManager->shouldReceive('save')->once();
    $this->client->shouldReceive('newClientInstance')
        ->andReturnUsing()->once()->andReturnSelf();
    $this->client->shouldReceive('setActive')
        ->once()
        ->with(true)
        ->andReturnSelf();
    $this->client->shouldReceive('setAllowPlainTextPkce')
        ->once()
        ->andReturnSelf();
    $this->client->shouldReceive('setRedirectUris')
        ->once()
        ->andReturnSelf();
    $this->client->shouldReceive('setGrants')
        ->once()
        ->andReturnSelf();
    $this->client->shouldReceive('setScopes')
        ->once()
        ->andReturnSelf();
    $this->client->shouldReceive('getIdentifier')
        ->once()
        ->andReturn('custom-id');
    $this->client->shouldReceive('getSecret')
        ->once()->andReturn(null);

    $input = new ArrayInput([
        'name' => 'test-client',
        '--allow-plain-text-pkce' => true,
    ]);
    $output = new BufferedOutput();

    $this->command->run($input, $output);
});
