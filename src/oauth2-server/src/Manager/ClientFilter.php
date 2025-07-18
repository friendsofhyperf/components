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

use FriendsOfHyperf\Oauth2\Server\ValueObject\Grant;
use FriendsOfHyperf\Oauth2\Server\ValueObject\RedirectUri;
use FriendsOfHyperf\Oauth2\Server\ValueObject\Scope;

final class ClientFilter
{
    /**
     * @var list<Grant>
     */
    private $grants = [];

    /**
     * @var list<RedirectUri>
     */
    private $redirectUris = [];

    /**
     * @var list<Scope>
     */
    private $scopes = [];

    public static function create(): self
    {
        return new self();
    }

    public function addGrantCriteria(Grant ...$grants): self
    {
        foreach ($grants as $grant) {
            $this->grants[] = $grant;
        }

        return $this;
    }

    public function addRedirectUriCriteria(RedirectUri ...$redirectUris): self
    {
        foreach ($redirectUris as $redirectUri) {
            $this->redirectUris[] = $redirectUri;
        }

        return $this;
    }

    public function addScopeCriteria(Scope ...$scopes): self
    {
        foreach ($scopes as $scope) {
            $this->scopes[] = $scope;
        }

        return $this;
    }

    /**
     * @return list<Grant>
     */
    public function getGrants(): array
    {
        return $this->grants;
    }

    /**
     * @return list<RedirectUri>
     */
    public function getRedirectUris(): array
    {
        return $this->redirectUris;
    }

    /**
     * @return list<Scope>
     */
    public function getScopes(): array
    {
        return $this->scopes;
    }

    public function hasFilters(): bool
    {
        return
            ! empty($this->grants)
            || ! empty($this->redirectUris)
            || ! empty($this->scopes);
    }
}
