<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Oauth2\Server\Enums;

enum OAuth2Grants: string
{
    /**
     * @see https://tools.ietf.org/html/rfc6749#section-1.3.1
     */
    case AuthorizationCode = 'authorization_code';

    /**
     * @see https://tools.ietf.org/html/rfc6749#section-1.3.4
     */
    case ClientCredentials = 'client_credentials';

    /**
     * @see https://tools.ietf.org/html/rfc6749#section-1.3.2
     */
    case Implicit = 'implicit';

    /**
     * @see https://tools.ietf.org/html/rfc6749#section-1.3.3
     */
    case Password = 'password';

    /**
     * @see https://tools.ietf.org/html/rfc6749#section-6
     */
    case RefreshToken = 'refresh_token';

    /**
     * @see https://tools.ietf.org/html/rfc8628#section-3.1
     */
    case DeviceCode = 'device_code';
}
