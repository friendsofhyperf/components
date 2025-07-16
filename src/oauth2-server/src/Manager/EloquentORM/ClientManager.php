<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Oauth2\Server\Manager\EloquentORM;

use FriendsOfHyperf\Oauth2\Server\Event\PreSaveClientEvent;
use FriendsOfHyperf\Oauth2\Server\Manager\ClientFilter;
use FriendsOfHyperf\Oauth2\Server\Manager\ClientManagerInterface;
use FriendsOfHyperf\Oauth2\Server\Model\Client;
use FriendsOfHyperf\Oauth2\Server\Model\ClientInterface;
use FriendsOfHyperf\Oauth2\Server\ValueObject\Grant;
use FriendsOfHyperf\Oauth2\Server\ValueObject\RedirectUri;
use FriendsOfHyperf\Oauth2\Server\ValueObject\Scope;
use Psr\EventDispatcher\EventDispatcherInterface;

final class ClientManager implements ClientManagerInterface
{
    public function __construct(
        private readonly EventDispatcherInterface $eventDispatcher
    ) {
    }

    public function save(ClientInterface $client): void
    {
        $event = new PreSaveClientEvent($client);
        $this->eventDispatcher->dispatch($event);
        $client = $event->getClient();
        // @phpstan-ignore-next-line
        $client->save();
    }

    public function remove(ClientInterface $client): void
    {
        // // @phpstan-ignore-next-line
        $client->delete();
    }

    public function find(string $identifier): ?ClientInterface
    {
        // @phpstan-ignore-next-line
        return Client::find($identifier);
    }

    /**
     * @return list<ClientInterface>
     */
    public function list(?ClientFilter $clientFilter): array
    {
        $criteria = self::filterToCriteria($clientFilter);
        $query = Client::query();
        // @phpstan-ignore-next-line
        if (! empty($criteria['grants'])) {
            $query->where(function ($query) use ($criteria) {
                foreach ($criteria['grants'] as $grant) {
                    $query->orWhereJsonContains('grants', (string) $grant);
                }
            });
        }
        // @phpstan-ignore-next-line
        if (! empty($criteria['redirects'])) {
            $query->where(function ($query) use ($criteria) {
                // @phpstan-ignore-next-line
                foreach ($criteria['redirects'] as $redirect) {
                    $query->orWhereJsonContains('redirects', (string) $redirect);
                }
            });
        }
        // @phpstan-ignore-next-line
        if (! empty($criteria['scopes'])) {
            $query->where(function ($query) use ($criteria) {
                foreach ($criteria['scopes'] as $scope) {
                    $query->orWhereJsonContains('scopes', (string) $scope);
                }
            });
        }
        // @phpstan-ignore-next-line
        return $query->get()->all();
    }

    /**
     * @return array{grants?: list<Grant>, redirect_uris?: list<RedirectUri>, scopes?: list<Scope>}
     */
    private static function filterToCriteria(?ClientFilter $clientFilter): array
    {
        if ($clientFilter === null || $clientFilter->hasFilters() === false) {
            return [];
        }

        $criteria = [];

        $grants = $clientFilter->getGrants();
        if ($grants) {
            $criteria['grants'] = $grants;
        }

        $redirectUris = $clientFilter->getRedirectUris();
        if ($redirectUris) {
            $criteria['redirects'] = $redirectUris;
        }

        $scopes = $clientFilter->getScopes();
        if ($scopes) {
            $criteria['scopes'] = $scopes;
        }

        return $criteria;
    }
}
