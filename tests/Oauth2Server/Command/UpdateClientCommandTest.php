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

use FriendsOfHyperf\Oauth2\Server\Command\UpdateClientCommand;
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
    $this->client = Mockery::mock(ClientInterface::class);
    $configMock = $this->createMock(ConfigInterface::class);
    ApplicationContext::getContainer()->set(ConfigInterface::class, $configMock);
    $configMock->method('get')->willReturn([]);

    $this->command = new UpdateClientCommand($this->clientManager);
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

it('updates client successfully', function () {
    $identifier = 'test-client';
    $this->clientManager->shouldReceive('find')->with($identifier)->andReturn($this->client);
    $this->client->shouldReceive('isActive')->andReturn(true);
    $this->client->shouldReceive('setActive')->once()->with(true);
    $this->client->shouldReceive('getRedirectUris')->andReturn([]);
    $this->client->shouldReceive('getGrants')->andReturn([]);
    $this->client->shouldReceive('getScopes')->andReturn([]);
    $this->client->shouldReceive('setRedirectUris')->once();
    $this->client->shouldReceive('setGrants')->once();
    $this->client->shouldReceive('setScopes')->once();
    $this->clientManager->shouldReceive('save')->once();

    $input = new ArrayInput(['identifier' => $identifier]);
    $output = new BufferedOutput();

    $exitCode = $this->command->run($input, $output);
    expect($exitCode)->toBe(0)
        ->and($output->fetch())->toContain('OAuth2 client updated successfully.');
});

it('adds redirect uri', function () {
    $identifier = 'test-client';
    $newUri = 'https://new.uri';
    $existingUri = 'https://exist.uri';

    $redirectUriMock = new RedirectUri($existingUri);

    $this->clientManager->shouldReceive('find')->with($identifier)->andReturn($this->client);
    $this->client->shouldReceive('isActive')->andReturn(true);
    $this->client->shouldReceive('setActive')->once()->with(true);
    $this->client->shouldReceive('getRedirectUris')->andReturn([$redirectUriMock]);
    $this->client->shouldReceive('getGrants')->andReturn([]);
    $this->client->shouldReceive('setGrants')->once()->andReturnSelf();
    $this->client->shouldReceive('setScopes')->once()->andReturnSelf();
    $this->client->shouldReceive('getScopes')->andReturn([]);
    $this->client->shouldReceive('setRedirectUris')->once()
        ->withArgs(function (...$uris) use ($existingUri, $newUri) {
            expect($uris)->toHaveCount(2)
                ->and((string) $uris[0])->toBe($existingUri)
                ->and((string) $uris[1])->toBe($newUri);
            return true;
        });
    $this->clientManager->shouldReceive('save')->once();

    $input = new ArrayInput([
        'identifier' => $identifier,
        '--add-redirect-uri' => [$newUri],
        '--disable-event-dispatcher' => true,
    ]);
    $output = new BufferedOutput();

    $this->command->run($input, $output);
});

it('removes redirect uri', function () {
    $identifier = 'test-client';
    $removeUri = 'https://remove.uri';

    $redirectUriMock = new RedirectUri($removeUri);

    $this->clientManager->shouldReceive('find')->with($identifier)->andReturn($this->client);
    $this->client->shouldReceive('isActive')->andReturn(true);
    $this->client->shouldReceive('setActive')->once()->with(true);
    $this->client->shouldReceive('setGrants')->once()->andReturnSelf();
    $this->client->shouldReceive('setScopes')->once()->andReturnSelf();
    $this->client->shouldReceive('getRedirectUris')->andReturn([$redirectUriMock]);
    $this->client->shouldReceive('getGrants')->andReturn([]);
    $this->client->shouldReceive('getScopes')->andReturn([]);
    $this->client->shouldReceive('setRedirectUris')->once()->withArgs(function (...$uris) {
        expect($uris)->toBeEmpty();
        return true;
    })->andReturnSelf();
    $this->clientManager->shouldReceive('save')->once();

    $input = new ArrayInput([
        'identifier' => $identifier,
        '--remove-redirect-uri' => [$removeUri],
        '--disable-event-dispatcher' => true,
    ]);
    $output = new BufferedOutput();

    $this->command->run($input, $output);
});

it('throws exception when adding and removing same redirect uri', function () {
    $identifier = 'test-client';
    $uri = 'https://same.uri';

    $redirectUriMock = new RedirectUri($uri);
    $this->clientManager->shouldReceive('find')->with($identifier)->andReturn($this->client);
    $this->client->shouldReceive('isActive')->andReturn(true);
    $this->client->shouldReceive('setActive')->andReturnSelf();
    $this->client->shouldReceive('getRedirectUris')->andReturn([$redirectUriMock]);
    $this->client->shouldReceive('getGrants')->andReturn([]);
    $this->client->shouldReceive('setScopes')->andReturnSelf();
    $this->client->shouldReceive('setGrants')->andReturnSelf();
    $this->client->shouldReceive('getScopes')->andReturn([]);

    $input = new ArrayInput([
        'identifier' => $identifier,
        '--add-redirect-uri' => [$uri],
        '--remove-redirect-uri' => [$uri],
        '--disable-event-dispatcher' => true,
    ]);
    $output = new BufferedOutput();
    $this->expectExceptionMessage('Cannot specify "' . $uri . '" in either "--add-redirect-uri" and "--remove-redirect-uri"');
    $exitCode = $this->command->run($input, $output);
    expect($exitCode)->toBe(1)
        ->and($output->fetch())->toContain('Cannot specify "' . $uri . '" in either "--add-redirect-uri" and "--remove-redirect-uri"');
});

it('adds grant type', function () {
    $identifier = 'test-client';
    $newGrant = 'authorization_code';

    $grantMock = new Grant($newGrant);

    $this->clientManager->shouldReceive('find')->with($identifier)->andReturn($this->client);
    $this->client->shouldReceive('isActive')->andReturn(true);
    $this->client->shouldReceive('setActive')->once()->with(true);
    $this->client->shouldReceive('getRedirectUris')->andReturn([]);
    $this->client->shouldReceive('setRedirectUris')->andReturnSelf();
    $this->client->shouldReceive('setScopes')->andReturnSelf();
    $this->client->shouldReceive('getGrants')->andReturn([]);
    $this->client->shouldReceive('getScopes')->andReturn([]);
    $this->client->shouldReceive('setGrants')->once()
        ->with(Mockery::on(function (...$arg) use ($newGrant) {
            return count($arg) === 1
                && $arg[0]->__toString() === $newGrant;
        }));
    $this->clientManager->shouldReceive('save')->once();

    $input = new ArrayInput([
        'identifier' => $identifier,
        '--add-grant-type' => [$newGrant],
        '--disable-event-dispatcher' => true,
    ]);
    $output = new BufferedOutput();

    $this->command->run($input, $output);
});

it('removes grant type', function () {
    $identifier = 'test-client';
    $removeGrant = 'client_credentials';
    $grantMock = new Grant($removeGrant);

    $this->clientManager->shouldReceive('find')->with($identifier)->andReturn($this->client);
    $this->client->shouldReceive('isActive')->andReturn(true);
    $this->client->shouldReceive('setActive')->once()->with(true);
    $this->client->shouldReceive('getRedirectUris')->andReturn([]);
    $this->client->shouldReceive('setRedirectUris')->andReturnSelf();
    $this->client->shouldReceive('setScopes')->andReturnSelf();
    $this->client->shouldReceive('getGrants')->andReturn([$grantMock]);
    $this->client->shouldReceive('getScopes')->andReturn([]);
    $this->client->shouldReceive('setGrants')->once()
        ->withArgs(function (...$grants) {
            expect($grants)->toBeEmpty();
            return true;
        })->andReturnSelf();
    $this->clientManager->shouldReceive('save')->once();

    $input = new ArrayInput([
        'identifier' => $identifier,
        '--remove-grant-type' => [$removeGrant],
        '--disable-event-dispatcher' => true,
    ]);
    $output = new BufferedOutput();

    $this->command->run($input, $output);
});

it('adds scope', function () {
    $identifier = 'test-client';
    $newScope = 'new_scope';
    $scope = new Scope($newScope);

    $this->clientManager->shouldReceive('find')->with($identifier)->andReturn($this->client);
    $this->client->shouldReceive('isActive')->andReturn(true);
    $this->client->shouldReceive('setActive')->once()->with(true);
    $this->client->shouldReceive('getRedirectUris')->andReturn([]);
    $this->client->shouldReceive('setRedirectUris')->andReturnSelf();
    $this->client->shouldReceive('setGrants')->andReturnSelf();
    $this->client->shouldReceive('getGrants')->andReturn([]);
    $this->client->shouldReceive('getScopes')->andReturn([]);
    $this->client->shouldReceive('setScopes')->once()
        ->with(Mockery::on(function (...$arg) use ($newScope) {
            return count($arg) === 1
                && $arg[0]->__toString() === $newScope;
        }))->andReturnSelf();
    $this->clientManager->shouldReceive('save')->once();

    $input = new ArrayInput([
        'identifier' => $identifier,
        '--add-scope' => [$newScope],
        '--disable-event-dispatcher' => true,
    ]);
    $output = new BufferedOutput();

    $this->command->run($input, $output);
});

it('removes scope', function () {
    $identifier = 'test-client';
    $removeScope = 'remove_scope';
    $scopeMock = new Scope($removeScope);

    $this->clientManager->shouldReceive('find')->with($identifier)->andReturn($this->client);
    $this->client->shouldReceive('isActive')->andReturn(true);
    $this->client->shouldReceive('setActive')->once()->with(true);
    $this->client->shouldReceive('getRedirectUris')->andReturn([]);
    $this->client->shouldReceive('setRedirectUris')->once()->andReturnSelf();
    $this->client->shouldReceive('setGrants')->once()->andReturnSelf();
    $this->client->shouldReceive('getGrants')->andReturn([]);
    $this->client->shouldReceive('getScopes')->andReturn([$scopeMock]);
    $this->client->shouldReceive('setScopes')->once()->andReturnUsing(function (...$scopes) {
        expect($scopes)->toBeEmpty();
        return $this->client;
    });
    $this->clientManager->shouldReceive('save')->once();

    $input = new ArrayInput([
        'identifier' => $identifier,
        '--remove-scope' => [$removeScope],
        '--disable-event-dispatcher' => true,
    ]);
    $output = new BufferedOutput();

    $this->command->run($input, $output);
});

it('activates client', function () {
    $identifier = 'test-client';

    $this->clientManager->shouldReceive('find')->with($identifier)->andReturn($this->client);
    $this->client->shouldReceive('isActive')->andReturn(false);
    $this->client->shouldReceive('setActive')->once()->with(true);
    $this->client->shouldReceive('getRedirectUris')->andReturn([]);
    $this->client->shouldReceive('getGrants')->andReturn([]);
    $this->client->shouldReceive('getScopes')->andReturn([]);
    $this->client->shouldReceive('setRedirectUris')->once();
    $this->client->shouldReceive('setGrants')->once();
    $this->client->shouldReceive('setScopes')->once();
    $this->clientManager->shouldReceive('save')->once();

    $input = new ArrayInput([
        'identifier' => $identifier,
        '--activate' => true,
    ]);
    $output = new BufferedOutput();

    $this->command->run($input, $output);
});

it('deactivates client', function () {
    $identifier = 'test-client';

    $this->clientManager->shouldReceive('find')->with($identifier)->andReturn($this->client);
    $this->client->shouldReceive('isActive')->andReturn(true);
    $this->client->shouldReceive('setActive')->once()->with(false)->andReturnSelf();
    $this->client->shouldReceive('getRedirectUris')->andReturn([]);
    $this->client->shouldReceive('getGrants')->andReturn([]);
    $this->client->shouldReceive('getScopes')->andReturn([]);
    $this->client->shouldReceive('setRedirectUris')->once();
    $this->client->shouldReceive('setGrants')->once();
    $this->client->shouldReceive('setScopes')->once();
    $this->clientManager->shouldReceive('save')->once();

    $input = new ArrayInput([
        'identifier' => $identifier,
        '--deactivate' => true,
        '--disable-event-dispatcher' => true,
    ]);
    $input->setInteractive(false);
    $output = new BufferedOutput();

    $this->command->run($input, $output);
});

it('throws exception when using activate and deactivate together', function () {
    $identifier = 'test-client';

    $this->clientManager->shouldReceive('find')->with($identifier)->andReturn($this->client);
    $this->client->shouldReceive('isActive')->andReturn(true);

    $input = new ArrayInput([
        'identifier' => $identifier,
        '--activate' => true,
        '--deactivate' => true,
        '--disable-event-dispatcher' => true,
    ]);
    $output = new BufferedOutput();
    $this->expectExceptionMessage('Cannot specify "--activate" and "--deactivate" at the same time.');
    $this->command->run($input, $output);
});
