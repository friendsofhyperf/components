<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Oauth2\Server\Event;

use FriendsOfHyperf\Oauth2\Server\Converter\ScopeConverterInterface;
use FriendsOfHyperf\Oauth2\Server\Interfaces\SecurityInterface;
use FriendsOfHyperf\Oauth2\Server\Manager\ClientManagerInterface;

final class AuthorizationRequestResolveEventFactory
{
    use AuthorizationRequestResolveEventFactoryTrait;

    public function __construct(
        SecurityInterface $security,
        ClientManagerInterface $clientManager,
        ScopeConverterInterface $scopeConverter
    ) {
        $this->clientManager = $clientManager;
        $this->scopeConverter = $scopeConverter;
        $this->security = $security;
    }
}
