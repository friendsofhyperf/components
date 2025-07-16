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
use FriendsOfHyperf\Oauth2\Server\Manager\RefreshTokenManagerInterface;
use FriendsOfHyperf\Oauth2\Server\Model\RefreshToken;
use FriendsOfHyperf\Oauth2\Server\Model\RefreshTokenInterface;

final class RefreshTokenManager implements RefreshTokenManagerInterface
{
    public function find(string $identifier): ?RefreshTokenInterface
    {
        return RefreshToken::find($identifier);
    }

    public function save(RefreshTokenInterface $refreshToken): void
    {
        $refreshToken->save();
    }

    public function clearExpired(): int
    {
        return RefreshToken::query()
            ->where('expires_at', '<', Carbon::now())
            ->delete();
    }
}
