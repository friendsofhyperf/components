<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Oauth2\Server\Manager\ElquentORM;

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
        $client->save();
    }

    public function remove(ClientInterface $client): void
    {
        $client->delete();
    }

    public function find(string $identifier): ?ClientInterface
    {
        return Client::find($identifier);
    }

    /**
     * @return list<ClientInterface>
     */
    public function list(?ClientFilter $clientFilter): array
    {
        $criteria = self::filterToCriteria($clientFilter);
        $query = Client::query();
        if (! empty($criteria['grants'])) {
            $query->whereIn('grants', $criteria['grants']);
        }
        if (! empty($criteria['redirects'])) {
            $query->whereIn('redirects', $criteria['redirects']);
        }
        if (! empty($criteria['scopes'])) {
            $query->whereIn('scopes', $criteria['scopes']);
        }
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
            $criteria['redirect_uris'] = $redirectUris;
        }

        $scopes = $clientFilter->getScopes();
        if ($scopes) {
            $criteria['scopes'] = $scopes;
        }

        return $criteria;
    }
}
