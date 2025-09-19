<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Oauth2\Server\Repository;

use FriendsOfHyperf\Oauth2\Server\Event\UserResolveEvent;
use FriendsOfHyperf\Oauth2\Server\Manager\ClientManagerInterface;
use FriendsOfHyperf\Oauth2\Server\Model\ClientInterface;
use FriendsOfHyperf\Oauth2\Server\ValueObject\Grant;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\UserEntityInterface;
use League\OAuth2\Server\Repositories\UserRepositoryInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

final class UserRepository implements UserRepositoryInterface
{
    public function __construct(
        private readonly ClientManagerInterface $clientManager,
        private readonly EventDispatcherInterface $eventDispatcher
    ) {
    }

    /**
     * @param non-empty-string $grantType
     */
    public function getUserEntityByUserCredentials(
        string $username,
        string $password,
        string $grantType,
        ClientEntityInterface $clientEntity,
    ): ?UserEntityInterface {
        /** @var null|ClientInterface $client */
        $client = $this->clientManager->find($clientEntity->getIdentifier());

        if ($client === null) {
            return null;
        }

        $event = $this->eventDispatcher->dispatch(
            new UserResolveEvent(
                $username,
                $password,
                new Grant($grantType),
                $client
            )
        );

        $user = $event->getUser();

        if ($user === null) {
            return null;
        }

        return $user;
    }
}
