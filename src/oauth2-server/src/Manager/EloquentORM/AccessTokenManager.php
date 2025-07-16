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
use FriendsOfHyperf\Oauth2\Server\Interfaces\ConfigInterface;
use FriendsOfHyperf\Oauth2\Server\Manager\AccessTokenManagerInterface;
use FriendsOfHyperf\Oauth2\Server\Model\AccessToken;
use FriendsOfHyperf\Oauth2\Server\Model\AccessTokenInterface;

final class AccessTokenManager implements AccessTokenManagerInterface
{
    public function __construct(
        private readonly ConfigInterface $config
    ) {
    }

    public function find(string $identifier): ?AccessTokenInterface
    {
        if (! $this->persistAccessToken()) {
            return null;
        }
        // @phpstan-ignore-next-line
        return AccessToken::find($identifier);
    }

    public function save(AccessTokenInterface $accessToken): void
    {
        if (! $this->persistAccessToken()) {
            return;
        }
        // @phpstan-ignore-next-line
        $accessToken->save();
    }

    public function clearExpired(): int
    {
        if (! $this->persistAccessToken()) {
            return 0;
        }
        return AccessToken::query()
            ->where('expires_at', '<', Carbon::now())
            ->delete();
    }

    private function persistAccessToken(): bool
    {
        return (bool) $this->config->get('authorization_server.persist_access_token', false);
    }
}
