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

use FriendsOfHyperf\Oauth2\Server\Command\ListClientsCommand;
use FriendsOfHyperf\Oauth2\Server\Manager\ClientFilter;
use FriendsOfHyperf\Oauth2\Server\Manager\ClientManagerInterface;
use FriendsOfHyperf\Oauth2\Server\Model\ClientInterface;
use FriendsOfHyperf\Oauth2\Server\ValueObject\Grant;
use FriendsOfHyperf\Oauth2\Server\ValueObject\RedirectUri;
use FriendsOfHyperf\Oauth2\Server\ValueObject\Scope;
use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\ConfigInterface;
use Mockery;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

uses()->group('oauth2');

beforeEach(function () {
    $this->clientManager = Mockery::mock(ClientManagerInterface::class);
    $configMock = $this->createMock(ConfigInterface::class);
    ApplicationContext::getContainer()->set(ConfigInterface::class, $configMock);
    $configMock->method('get')->willReturn([]);

    $this->command = new ListClientsCommand($this->clientManager);
});

afterEach(function () {
    ApplicationContext::getContainer()->unbind(ConfigInterface::class);
    Mockery::close();
});

it('lists clients with default options', function () {
    $this->clientManager->shouldReceive('list')
        ->once()
        ->withArgs(function ($filter) {
            expect($filter->getGrants())->toBeEmpty();
            expect($filter->getRedirectUris())->toBeEmpty();
            expect($filter->getScopes())->toBeEmpty();
            return true;
        })
        ->andReturn([]);

    $input = new ArrayInput([]);
    $output = new BufferedOutput();

    $exitCode = $this->command->run($input, $output);
    expect($exitCode)->toBe(0);
});

it('filters by redirect uri', function () {
    $redirectUri = 'https://example.com/callback';

    $this->clientManager->shouldReceive('list')
        ->once()
        ->withArgs(function ($filter) use ($redirectUri) {
            $uris = array_map(fn (RedirectUri $uri) => (string) $uri, $filter->getRedirectUris());
            expect($uris)->toContain($redirectUri);
            return true;
        })
        ->andReturn([]);

    $input = new ArrayInput(['--redirect-uri' => $redirectUri]);
    $output = new BufferedOutput();

    $exitCode = $this->command->run($input, $output);
    expect($exitCode)->toBe(0);
});

it('filters by multiple redirect uris', function () {
    $uris = ['https://example.com/callback1', 'https://example.com/callback2'];

    $this->clientManager->shouldReceive('list')
        ->once()
        ->withArgs(function (ClientFilter $filter) use ($uris) {
            $redirectUris = array_map(fn (RedirectUri $uri) => (string) $uri, $filter->getRedirectUris());
            expect($redirectUris)->toEqual($uris);
            return true;
        })
        ->andReturn([]);

    $input = new ArrayInput(['--redirect-uri' => $uris]);
    $output = new BufferedOutput();

    $exitCode = $this->command->run($input, $output);
    expect($exitCode)->toBe(0);
});

it('filters by grant type', function () {
    $grantType = 'authorization_code';

    $this->clientManager->shouldReceive('list')
        ->once()
        ->withArgs(function (ClientFilter $filter) use ($grantType) {
            $grantTypes = array_map(fn (Grant $grant) => (string) $grant, $filter->getGrants());
            expect($grantTypes)->toContain($grantType);
            return true;
        })
        ->andReturn([]);

    $input = new ArrayInput(['--grant-type' => $grantType]);
    $output = new BufferedOutput();

    $exitCode = $this->command->run($input, $output);
    expect($exitCode)->toBe(0);
});

it('filters by multiple grant types', function () {
    $grants = ['authorization_code', 'client_credentials'];

    $this->clientManager->shouldReceive('list')
        ->once()
        ->withArgs(function ($filter) use ($grants) {
            $grantObjects = array_map(fn ($grant) => new Grant($grant), $grants);
            expect($filter->getGrants())->toEqual($grantObjects);
            return true;
        })
        ->andReturn([]);

    $input = new ArrayInput(['--grant-type' => $grants]);
    $output = new BufferedOutput();

    $exitCode = $this->command->run($input, $output);
    expect($exitCode)->toBe(0);
});

it('filters by scope', function () {
    $scope = 'read';

    $this->clientManager->shouldReceive('list')
        ->once()
        ->withArgs(function (ClientFilter $filter) use ($scope) {
            $scopes = array_map(fn (Scope $g1) => (string) $g1, $filter->getScopes());
            expect($scopes)->toContain($scope);
            return true;
        })
        ->andReturn([]);

    $input = new ArrayInput(['--scope' => $scope]);
    $output = new BufferedOutput();

    $exitCode = $this->command->run($input, $output);
    expect($exitCode)->toBe(0);
});

it('filters by multiple scopes', function () {
    $scopes = ['read', 'write'];

    $this->clientManager->shouldReceive('list')
        ->once()
        ->withArgs(function (ClientFilter $filter) use ($scopes) {
            $scopesStrings = array_map(fn ($scope) => (string) $scope, $filter->getScopes());
            expect($scopesStrings)->toEqual($scopes);
            return true;
        })
        ->andReturn([]);

    $input = new ArrayInput(['--scope' => $scopes]);
    $output = new BufferedOutput();

    $exitCode = $this->command->run($input, $output);
    expect($exitCode)->toBe(0);
});

it('displays selected columns', function () {
    $client = Mockery::mock(ClientInterface::class);
    $client->shouldReceive('getName')->andReturn('test-client');
    $client->shouldReceive('getIdentifier')->andReturn('client-id');
    $client->shouldReceive('getSecret')->andReturn('secret');
    $client->shouldReceive('getScopes')->andReturn(['read']);
    $client->shouldReceive('getRedirectUris')->andReturn(['https://example.com']);
    $client->shouldReceive('getGrants')->andReturn(['authorization_code']);

    $this->clientManager->shouldReceive('list')->andReturn([$client]);

    $input = new ArrayInput(['--columns' => ['name', 'identifier']]);
    $output = new BufferedOutput();

    $exitCode = $this->command->run($input, $output);
    $outputContent = $output->fetch();

    expect($exitCode)->toBe(0)
        ->and($outputContent)->toContain('name          identifier')
        ->and($outputContent)->toContain('test-client   client-id');
});

it('ignores invalid columns', function () {
    $client = Mockery::mock(ClientInterface::class);
    $client->shouldReceive('getName')->andReturn('test-client');
    $client->shouldReceive('getIdentifier')->andReturn('client-id');
    $client->shouldReceive('getSecret')->andReturn('secret');
    $client->shouldReceive('getScopes')->andReturn([new Scope('read')]);
    $client->shouldReceive('getRedirectUris')->andReturn([new RedirectUri('https://example.com')]);
    $client->shouldReceive('getGrants')->andReturn([new Grant('authorization_code')]);

    $this->clientManager->shouldReceive('list')->andReturn([$client]);

    $input = new ArrayInput(['--columns' => ['invalid', 'name']]);
    $output = new BufferedOutput();

    $exitCode = $this->command->run($input, $output);
    $outputContent = $output->fetch();
    expect($exitCode)->toBe(0)
        ->and($outputContent)->toContain('name')
        ->and($outputContent)->toContain('test-client');
});
