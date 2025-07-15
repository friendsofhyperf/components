<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Oauth2\Server\Manager;

use FriendsOfHyperf\Oauth2\Server\Model\ClientInterface;

interface ClientManagerInterface
{
    public function save(ClientInterface $client): void;

    public function remove(ClientInterface $client): void;

    public function find(string $identifier): ?ClientInterface;

    /**
     * @return list<ClientInterface>
     */
    public function list(?ClientFilter $clientFilter): array;
}
