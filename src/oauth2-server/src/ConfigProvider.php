<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Oauth2\Server;

use FriendsOfHyperf\Oauth2\Server\Command\ClearExpiredTokensCommand;
use FriendsOfHyperf\Oauth2\Server\Command\CreateClientCommand;
use FriendsOfHyperf\Oauth2\Server\Command\DeleteClientCommand;
use FriendsOfHyperf\Oauth2\Server\Command\GenerateKeyPairCommand;
use FriendsOfHyperf\Oauth2\Server\Command\ListClientsCommand;
use FriendsOfHyperf\Oauth2\Server\Command\UpdateClientCommand;
use FriendsOfHyperf\Oauth2\Server\Converter\ClientConverter;
use FriendsOfHyperf\Oauth2\Server\Converter\ClientConverterInterface;
use FriendsOfHyperf\Oauth2\Server\Converter\ScopeConverter;
use FriendsOfHyperf\Oauth2\Server\Converter\ScopeConverterInterface;
use FriendsOfHyperf\Oauth2\Server\Factory\AuthorizationServerFactory;
use FriendsOfHyperf\Oauth2\Server\Factory\ConfigFactory;
use FriendsOfHyperf\Oauth2\Server\Factory\ResourceServerFactory;
use FriendsOfHyperf\Oauth2\Server\Interfaces\ConfigInterface;
use FriendsOfHyperf\Oauth2\Server\Manager\AccessTokenManagerInterface;
use FriendsOfHyperf\Oauth2\Server\Manager\AuthorizationCodeManagerInterface;
use FriendsOfHyperf\Oauth2\Server\Manager\ClientManagerInterface;
use FriendsOfHyperf\Oauth2\Server\Manager\DeviceCodeManagerInterface;
use FriendsOfHyperf\Oauth2\Server\Manager\EloquentORM\AccessTokenManager;
use FriendsOfHyperf\Oauth2\Server\Manager\EloquentORM\AuthorizationCodeManager;
use FriendsOfHyperf\Oauth2\Server\Manager\EloquentORM\ClientManager;
use FriendsOfHyperf\Oauth2\Server\Manager\EloquentORM\DeviceCodeManager;
use FriendsOfHyperf\Oauth2\Server\Manager\EloquentORM\RefreshTokenManager;
use FriendsOfHyperf\Oauth2\Server\Manager\InMemory\ScopeManager;
use FriendsOfHyperf\Oauth2\Server\Manager\RefreshTokenManagerInterface;
use FriendsOfHyperf\Oauth2\Server\Manager\ScopeManagerInterface;
use FriendsOfHyperf\Oauth2\Server\Model\AccessToken;
use FriendsOfHyperf\Oauth2\Server\Model\AccessTokenInterface;
use FriendsOfHyperf\Oauth2\Server\Model\Client;
use FriendsOfHyperf\Oauth2\Server\Model\ClientInterface;
use FriendsOfHyperf\Oauth2\Server\Model\RefreshToken;
use FriendsOfHyperf\Oauth2\Server\Model\RefreshTokenInterface;
use FriendsOfHyperf\Oauth2\Server\Repository\AccessTokenRepository;
use FriendsOfHyperf\Oauth2\Server\Repository\AuthCodeRepository;
use FriendsOfHyperf\Oauth2\Server\Repository\ClientRepository;
use FriendsOfHyperf\Oauth2\Server\Repository\DeviceCodeRepository;
use FriendsOfHyperf\Oauth2\Server\Repository\RefreshTokenRepository;
use FriendsOfHyperf\Oauth2\Server\Repository\ScopeRepository;
use FriendsOfHyperf\Oauth2\Server\Repository\UserRepository;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;
use League\OAuth2\Server\Repositories\AuthCodeRepositoryInterface;
use League\OAuth2\Server\Repositories\ClientRepositoryInterface;
use League\OAuth2\Server\Repositories\DeviceCodeRepositoryInterface;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;
use League\OAuth2\Server\Repositories\ScopeRepositoryInterface;
use League\OAuth2\Server\Repositories\UserRepositoryInterface;
use League\OAuth2\Server\ResourceServer;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'commands' => [
                ClearExpiredTokensCommand::class,
                CreateClientCommand::class,
                DeleteClientCommand::class,
                GenerateKeyPairCommand::class,
                ListClientsCommand::class,
                UpdateClientCommand::class,
            ],
            'dependencies' => [
                // models
                AccessTokenInterface::class => AccessToken::class,
                ClientInterface::class => Client::class,
                RefreshTokenInterface::class => RefreshToken::class,
                // managers
                AccessTokenManagerInterface::class => AccessTokenManager::class,
                AuthorizationCodeManagerInterface::class => AuthorizationCodeManager::class,
                ClientManagerInterface::class => ClientManager::class,
                RefreshTokenManagerInterface::class => RefreshTokenManager::class,
                ScopeManagerInterface::class => ScopeManager::class,
                DeviceCodeManagerInterface::class => DeviceCodeManager::class,
                // converters
                ScopeConverterInterface::class => ScopeConverter::class,
                ClientConverterInterface::class => ClientConverter::class,
                // repositories
                AccessTokenRepositoryInterface::class => AccessTokenRepository::class,
                AuthCodeRepositoryInterface::class => AuthCodeRepository::class,
                ClientRepositoryInterface::class => ClientRepository::class,
                RefreshTokenRepositoryInterface::class => RefreshTokenRepository::class,
                ScopeRepositoryInterface::class => ScopeRepository::class,
                UserRepositoryInterface::class => UserRepository::class,
                DeviceCodeRepositoryInterface::class => DeviceCodeRepository::class,
                // factroy
                ConfigInterface::class => ConfigFactory::class
            ],
            'publish' => [
                [
                    'id' => 'config',
                    'description' => 'The configuration file of oauth2 server.',
                    'source' => dirname(__DIR__) . '/publish/oauth2-server.php',
                    'destination' => BASE_PATH . '/config/autoload/oauth2-server.php',
                ],
            ],
        ];
    }
}
