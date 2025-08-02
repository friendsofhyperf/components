<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Oauth2\Server\Factory;

use Defuse\Crypto\Key;
use FriendsOfHyperf\Oauth2\Server\Enums\EncryptionKeyType;
use FriendsOfHyperf\Oauth2\Server\Interfaces\ConfigInterface;
use Hyperf\Contract\ContainerInterface;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\CryptKey;
use League\OAuth2\Server\Grant\AuthCodeGrant;
use League\OAuth2\Server\Grant\ClientCredentialsGrant;
use League\OAuth2\Server\Grant\ImplicitGrant;
use League\OAuth2\Server\Grant\PasswordGrant;
use League\OAuth2\Server\Grant\RefreshTokenGrant;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;
use League\OAuth2\Server\Repositories\ClientRepositoryInterface;
use League\OAuth2\Server\Repositories\ScopeRepositoryInterface;
use League\OAuth2\Server\RequestEvent;
use Psr\EventDispatcher\EventDispatcherInterface;
use RuntimeException;

class AuthorizationServerFactory
{
    public function __construct(
        private readonly ContainerInterface $container,
        private readonly ConfigInterface $config,
        private readonly ClientRepositoryInterface $clientRepository,
        private readonly AccessTokenRepositoryInterface $accessTokenRepository,
        private readonly ScopeRepositoryInterface $scopeRepository,
        private readonly EventDispatcherInterface $eventDispatcher
    ) {
    }

    public function build(): AuthorizationServer
    {
        $privateKey = $this->config->get('authorization_server.private_key');
        $privateKeyPassphrase = $this->config->get('authorization_server.private_key_passphrase');
        $responseType = $this->config->get('authorization_server.response_type');
        $encryptionKey = $this->config->get('authorization_server.encryption_key');
        $encryptionKeyType = $this->config->get('authorization_server.encryption_key_type');
        if ($encryptionKeyType === EncryptionKeyType::Defuse) {
            if (! class_exists(Key::class)) {
                throw new RuntimeException('You must install "defuse/php-encryption"');
            }
            $encryptionKey = Key::loadFromAsciiSafeString($encryptionKey);
        }
        $authorizationServer = new AuthorizationServer(
            $this->clientRepository,
            $this->accessTokenRepository,
            $this->scopeRepository,
            new CryptKey($privateKey, $privateKeyPassphrase, false),
            $encryptionKey,
            $this->container->get($responseType)
        );
        $authorizationServer->revokeRefreshTokens($this->config->get('authorization_server.revoke_refresh_tokens'));

        if ($this->config->get('authorization_server.enable_client_credentials_grant')) {
            $authorizationServer->enableGrantType(
                $this->container->get(ClientCredentialsGrant::class),
                $this->config->get('authorization_server.access_token_ttl')
            );
        }

        if ($this->config->get('authorization_server.enable_password_grant')) {
            $authorizationServer->enableGrantType(
                $this->container->get(PasswordGrant::class),
                $this->config->get('authorization_server.access_token_ttl')
            );
        }

        if ($this->config->get('authorization_server.enable_refresh_token_grant')) {
            $authorizationServer->enableGrantType(
                $this->container->get(RefreshTokenGrant::class),
                $this->config->get('authorization_server.access_token_ttl')
            );
        }

        if ($this->config->get('authorization_server.enable_auth_code_grant')) {
            $authCodeGrant = $this->container->make(
                AuthCodeGrant::class,
                [
                    'authCodeTTL' => $this->config->get('authorization_server.auth_code_ttl'),
                ]
            );
            $this->container->set(AuthCodeGrant::class, $authCodeGrant);
            $authorizationServer->enableGrantType(
                $authCodeGrant,
                $this->config->get('authorization_server.access_token_ttl')
            );
        }

        if ($this->config->get('authorization_server.enable_implicit_grant')) {
            $implicitGrant = $this->container->make(
                ImplicitGrant::class,
                [
                    'accessTokenTTL' => $this->config->get('authorization_server.access_token_ttl'),
                ]
            );
            $this->container->set(ImplicitGrant::class, $implicitGrant);
            $authorizationServer->enableGrantType(
                $implicitGrant,
                $this->config->get('authorization_server.access_token_ttl')
            );
        }
        $this->configureGrants();
        $this->configureListener($authorizationServer);
        return $authorizationServer;
    }

    private function configureGrants(): void
    {
        $refreshTokenTTL = $this->config->get('authorization_server.refresh_token_ttl');
        $this->container->get(PasswordGrant::class)
            ->setRefreshTokenTTL($refreshTokenTTL);
        $this->container->get(RefreshTokenGrant::class)
            ->setRefreshTokenTTL($refreshTokenTTL);
        $this->container->get(AuthCodeGrant::class)
            ->setRefreshTokenTTL($refreshTokenTTL);
        if (! $this->config->get('authorization_server.require_code_challenge_for_public_clients')) {
            $this->container->get(AuthCodeGrant::class)->disableRequireCodeChallengeForPublicClients();
        }
        if ($this->config->get('authorization_server.enable_implicit_grant')) {
            $this->container->get(ImplicitGrant::class)
                ->setRefreshTokenTTL($refreshTokenTTL);
        }
    }

    private function configureListener(AuthorizationServer $authorizationServer): void
    {
        $events = [
            RequestEvent::ACCESS_TOKEN_ISSUED,
            RequestEvent::CLIENT_AUTHENTICATION_FAILED,
            RequestEvent::REFRESH_TOKEN_ISSUED,
            RequestEvent::REFRESH_TOKEN_CLIENT_FAILED,
            RequestEvent::USER_AUTHENTICATION_FAILED,
        ];
        foreach ($events as $event) {
            $authorizationServer->getEmitter()
                ->addListener($event, function ($event) {
                    $this->eventDispatcher->dispatch($event);
                });
        }
    }
}
