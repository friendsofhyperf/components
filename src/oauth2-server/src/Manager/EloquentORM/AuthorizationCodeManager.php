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

use Carbon\Carbon;
use FriendsOfHyperf\Oauth2\Server\Manager\AuthorizationCodeManagerInterface;
use FriendsOfHyperf\Oauth2\Server\Model\AuthorizationCode;
use FriendsOfHyperf\Oauth2\Server\Model\AuthorizationCodeInterface;

final class AuthorizationCodeManager implements AuthorizationCodeManagerInterface
{
    public function find(string $identifier): ?AuthorizationCodeInterface
    {
        return AuthorizationCode::find($identifier);
    }

    public function save(AuthorizationCodeInterface $authCode): void
    {
        $authCode->save();
    }

    public function clearExpired(): int
    {
        return AuthorizationCode::query()
            ->where('expires_at', '<', Carbon::now())
            ->delete();
    }
}
