<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Oauth2\Server\Converter;

use FriendsOfHyperf\Oauth2\Server\Entity\Client as ClientEntity;
use FriendsOfHyperf\Oauth2\Server\Model\ClientInterface as ClientModel;

interface ClientConverterInterface
{
    public function toDomain(ClientEntity $client): ClientModel;

    public function toEntity(ClientModel $client): ClientEntity;
}
