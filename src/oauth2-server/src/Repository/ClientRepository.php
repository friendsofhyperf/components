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

use FriendsOfHyperf\Oauth2\Server\Entity\Client as ClientEntity;
use FriendsOfHyperf\Oauth2\Server\Manager\ClientManagerInterface;
use FriendsOfHyperf\Oauth2\Server\Model\ClientInterface;
use FriendsOfHyperf\Oauth2\Server\ValueObject\Grant;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\ClientRepositoryInterface;

use function in_array;

final class ClientRepository implements ClientRepositoryInterface
{
    public function __construct(
        private readonly ClientManagerInterface $clientManager
    ) {
    }

    public function getClientEntity(string $clientIdentifier): ?ClientEntityInterface
    {
        $client = $this->clientManager->find($clientIdentifier);

        if ($client === null) {
            return null;
        }

        return $this->buildClientEntity($client);
    }

    public function validateClient(string $clientIdentifier, ?string $clientSecret, ?string $grantType): bool
    {
        $client = $this->clientManager->find($clientIdentifier);

        if ($client === null) {
            return false;
        }

        if (! $client->isActive()) {
            return false;
        }

        if (! $this->isGrantSupported($client, $grantType)) {
            return false;
        }

        if (! $client->isConfidential() || hash_equals((string) $client->getSecret(), (string) $clientSecret)) {
            return true;
        }

        return false;
    }

    private function buildClientEntity(ClientInterface $client): ClientEntity
    {
        $clientEntity = new ClientEntity();
        $clientEntity->setName($client->getName());
        $clientEntity->setIdentifier($client->getIdentifier());
        $clientEntity->setRedirectUri(array_map('strval', $client->getRedirectUris()));
        $clientEntity->setConfidential($client->isConfidential());
        $clientEntity->setAllowPlainTextPkce($client->isPlainTextPkceAllowed());

        return $clientEntity;
    }

    private function isGrantSupported(ClientInterface $client, ?string $grant): bool
    {
        if ($grant === null) {
            return true;
        }

        $grants = array_map(fn (Grant $grantArg) => (string) $grantArg, $client->getGrants());

        if (empty($grants)) {
            return true;
        }

        return in_array($grant, $grants, true);
    }
}
