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

use FriendsOfHyperf\Oauth2\Server\Interfaces\ConfigInterface;
use Hyperf\Contract\ContainerInterface;
use League\OAuth2\Server\AuthorizationValidators\BearerTokenValidator;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;
use League\OAuth2\Server\ResourceServer;

class ResourceServerFactory
{
    public function __construct(
        private readonly ContainerInterface $container,
        private readonly ConfigInterface $config,
        private readonly AccessTokenRepositoryInterface $accessTokenRepository
    ) {
    }

    public function build(): ResourceServer
    {
        $publicKey = $this->config->get('resource_server.public_key');
        $bearerTokenValidator = $this->container->make(
            BearerTokenValidator::class,
            [
                'jwtValidAtDateLeeway' => $this->config->get('resource_server.jwt_leeway'),
            ]
        );
        $this->container->set(BearerTokenValidator::class, $bearerTokenValidator);
        return new ResourceServer(
            $this->accessTokenRepository,
            $publicKey,
            $bearerTokenValidator
        );
    }
}
