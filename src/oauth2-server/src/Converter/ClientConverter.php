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
use FriendsOfHyperf\Oauth2\Server\Model\Client as ClientModel;
use FriendsOfHyperf\Oauth2\Server\Model\ClientInterface;
use FriendsOfHyperf\Oauth2\Server\ValueObject\RedirectUri;

final class ClientConverter implements ClientConverterInterface
{
    public function toDomain(ClientEntity $client): ClientInterface
    {
        $clientModel = new ClientModel();
        $clientModel->name = $client->getName();
        $clientModel->id = $clientModel->getIdentifier();
        $clientModel->redirects = array_map(
            fn ($redirect) => new RedirectUri($redirect),
            $client->getRedirectUri()
        );
        $client->setAllowPlainTextPkce($client->isPlainTextPkceAllowed());
        return $clientModel;
    }

    public function toEntity(ClientInterface $client): ClientEntity
    {
        $clientEntity = new ClientEntity();
        $clientEntity->setName($client->getName());
        $clientEntity->setIdentifier($client->getIdentifier());
        $clientEntity->setRedirectUri(array_map('strval', $client->getRedirectUris()));
        $clientEntity->setConfidential($client->isConfidential());
        $clientEntity->setAllowPlainTextPkce($client->isPlainTextPkceAllowed());

        return $clientEntity;
    }
}
