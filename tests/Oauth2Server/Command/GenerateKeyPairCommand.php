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

use FriendsOfHyperf\Oauth2\Server\Command\GenerateKeyPairCommand;
use FriendsOfHyperf\Oauth2\Server\Interfaces\ConfigInterface as Oauth2ConfigInterface;
use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Support\Filesystem\Filesystem;
use Mockery;
use OpenSSLAsymmetricKey;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

uses()->group('oauth2');

beforeEach(function () {
    $this->filesystem = new Filesystem();
    $this->config = Mockery::mock(Oauth2ConfigInterface::class);
    $mockerConfig = Mockery::mock(ConfigInterface::class);
    $container = ApplicationContext::getContainer();

    // 配置默认密钥路径
    $this->config->shouldReceive('get')
        ->with('authorization_server.private_key', '')
        ->andReturn('/tmp/private.key');
    $this->config->shouldReceive('get')
        ->with('resource_server.public_key', '')
        ->andReturn('/tmp/public.key');
    $this->config->shouldReceive('get')
        ->with('authorization_server.private_key_passphrase', '')
        ->andReturnNull();
    $mockerConfig->shouldReceive('get')->with('listeners', [])->andReturn([]);
    $container->set(ConfigInterface::class, $mockerConfig);
    $container->set(Filesystem::class, $this->filesystem);

    $this->command = new GenerateKeyPairCommand($this->filesystem, $this->config);
});

afterEach(function () {
    if (file_exists('/tmp/private.key')) {
        unlink('/tmp/private.key');
    }
    if (file_exists('/tmp/public.key')) {
        unlink('/tmp/public.key');
    }
    ApplicationContext::getContainer()->unbind(ConfigInterface::class);
    ApplicationContext::getContainer()->unbind(Filesystem::class);
    Mockery::close();
});

it('uses default algorithm RS256 when not specified', function () {
    $input = new ArrayInput([]);
    $input->setInteractive(false);
    $output = new BufferedOutput();

    $exitCode = $this->command->run($input, $output);
    expect($exitCode)->toBe(0)
        ->and(file_exists('/tmp/private.key'))->toBeTrue()
        ->and(file_exists('/tmp/public.key'))->toBeTrue();
});

it('fails with unsupported algorithm', function () {
    $input = new ArrayInput(['algorithm' => 'HS255']);
    $output = new BufferedOutput();

    $exitCode = $this->command->run($input, $output);

    expect($exitCode)->toBe(1)
        ->and($output->fetch())->toContain('Cannot generate key pair with the provided algorithm');
});

it('works with dry-run option', function () {
    $input = new ArrayInput(['--dry-run' => true]);
    $output = new BufferedOutput();

    $exitCode = $this->command->run($input, $output);

    expect($exitCode)->toBe(0)
        ->and(file_exists('/tmp/private.key'))->toBeFalse()
        ->and(file_exists('/tmp/public.key'))->toBeFalse()
        ->and($output->fetch())->toContain('Update your private key in')
        ->toContain('Update your public key in');
});

it('skips generation when skip-if-exists is set and files exist', function () {
    touch('/tmp/private.key');
    touch('/tmp/public.key');

    $input = new ArrayInput(['--skip-if-exists' => true]);
    $output = new BufferedOutput();

    $exitCode = $this->command->run($input, $output);

    expect($exitCode)->toBe(0)
        ->and($output->fetch())->toContain('Your key files already exist, they won\'t be overridden.');
});

it('forces overwrite when overwrite option is set', function () {
    $input = new ArrayInput(['--overwrite' => true]);
    $output = new BufferedOutput();
    $input->setInteractive(false);

    $exitCode = $this->command->run($input, $output);

    expect($exitCode)->toBe(0);
});

it('applies passphrase to private key', function () {
    $passphrase = 'test-pass';
    $this->config->shouldReceive('get')
        ->with('authorization_server.private_key_passphrase')
        ->andReturn($passphrase);

    $input = new ArrayInput([]);
    $output = new BufferedOutput();

    $this->command->run($input, $output);

    $privateKey = file_get_contents('/tmp/private.key');
    expect(openssl_get_privatekey($privateKey, 'test-pass'))
        ->toBeObject()
        ->toBeInstanceOf(OpenSSLAsymmetricKey::class);
});

it('generates correct openssl config for ES512', function () {
    $input = new ArrayInput(['algorithm' => 'ES512']);
    $output = new BufferedOutput();

    $exitCode = $this->command->run($input, $output);

    $privateKey = file_get_contents('/tmp/private.key');
    expect($exitCode)->toBe(0)
        ->and($privateKey)->toContain('BEGIN PRIVATE KEY');
});
