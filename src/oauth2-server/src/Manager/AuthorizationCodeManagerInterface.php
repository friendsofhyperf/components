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

use FriendsOfHyperf\Oauth2\Server\Model\AuthorizationCodeInterface;

interface AuthorizationCodeManagerInterface
{
    public function find(string $identifier): ?AuthorizationCodeInterface;

    public function save(AuthorizationCodeInterface $authCode): void;

    public function clearExpired(): int;
}
