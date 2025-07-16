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

use FriendsOfHyperf\Oauth2\Server\Command\ClearExpiredTokensCommand;
use FriendsOfHyperf\Oauth2\Server\Manager\AccessTokenManagerInterface;
use FriendsOfHyperf\Oauth2\Server\Manager\AuthorizationCodeManagerInterface;
use FriendsOfHyperf\Oauth2\Server\Manager\RefreshTokenManagerInterface;
use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\ConfigInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

uses()->group('oauth2');

beforeEach(function () {
    $this->accessTokenManager = $this->createMock(AccessTokenManagerInterface::class);
    $this->refreshTokenManager = $this->createMock(RefreshTokenManagerInterface::class);
    $this->authorizationCodeManager = $this->createMock(AuthorizationCodeManagerInterface::class);
    $configMock = $this->createMock(ConfigInterface::class);
    ApplicationContext::getContainer()->set(ConfigInterface::class, $configMock);
    $configMock->method('get')->willReturn([]);
    $this->command = new ClearExpiredTokensCommand(
        $this->accessTokenManager,
        $this->refreshTokenManager,
        $this->authorizationCodeManager
    );
});

afterEach(function () {
    ApplicationContext::getContainer()->unbind(ConfigInterface::class);
});

test('clear all tokens when no options provided', function () {
    $input = new ArrayInput([]);
    $output = new BufferedOutput();

    $this->accessTokenManager->expects($this->once())->method('clearExpired')->willReturn(5);
    $this->refreshTokenManager->expects($this->once())->method('clearExpired')->willReturn(3);
    $this->authorizationCodeManager->expects($this->once())->method('clearExpired')->willReturn(2);

    $exitCode = $this->command->run($input, $output);
    $outputContent = $output->fetch();
    expect($exitCode)->toBe(0)
        ->and($outputContent)->toContain('Cleared 5 expired access tokens')
        ->and($outputContent)->toContain('Cleared 3 expired refresh tokens')
        ->and($outputContent)->toContain('Cleared 2 expired auth codes');
});

test('clear only access tokens', function () {
    $input = new ArrayInput(['--access-tokens' => true]);
    $output = new BufferedOutput();

    $this->accessTokenManager->expects($this->once())->method('clearExpired')->willReturn(5);
    $this->refreshTokenManager->expects($this->never())->method('clearExpired');
    $this->authorizationCodeManager->expects($this->never())->method('clearExpired');

    $exitCode = $this->command->run($input, $output);

    expect($exitCode)->toBe(0)
        ->and($output->fetch())->toContain('Cleared 5 expired access tokens');
});

test('clear multiple token types', function () {
    $input = new ArrayInput(['--access-tokens' => true, '--refresh-tokens' => true]);
    $output = new BufferedOutput();

    $this->accessTokenManager->expects($this->once())->method('clearExpired')->willReturn(5);
    $this->refreshTokenManager->expects($this->once())->method('clearExpired')->willReturn(3);
    $this->authorizationCodeManager->expects($this->never())->method('clearExpired');

    $exitCode = $this->command->run($input, $output);
    $outputContent = $output->fetch();
    expect($exitCode)->toBe(0)
        ->and($outputContent)->toContain('Cleared 5 expired access tokens')
        ->and($outputContent)->toContain('Cleared 3 expired refresh tokens');
});

test('no tokens cleared', function () {
    $input = new ArrayInput(['--access-tokens' => true]);
    $output = new BufferedOutput();

    $this->accessTokenManager->expects($this->once())->method('clearExpired')->willReturn(0);

    $exitCode = $this->command->run($input, $output);

    expect($exitCode)->toBe(0)
        ->and($output->fetch())->toContain('Cleared 0 expired access tokens');
});
