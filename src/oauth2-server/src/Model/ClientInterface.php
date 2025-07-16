<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Oauth2\Server\Model;

use FriendsOfHyperf\Oauth2\Server\ValueObject\Grant;
use FriendsOfHyperf\Oauth2\Server\ValueObject\RedirectUri;
use FriendsOfHyperf\Oauth2\Server\ValueObject\Scope;

interface ClientInterface
{
    /**
     * @return non-empty-string
     */
    public function getIdentifier(): string;

    public function getName(): string;

    public function getSecret(): ?string;

    /**
     * @return list<RedirectUri>
     */
    public function getRedirectUris(): array;

    public function setRedirectUris(RedirectUri ...$redirectUris): self;

    /**
     * @return list<Grant>
     */
    public function getGrants(): array;

    public function setGrants(Grant ...$grants): self;

    /**
     * @return list<Scope>
     */
    public function getScopes(): array;

    public function setScopes(Scope ...$scopes): self;

    public function isActive(): bool;

    public function setActive(bool $active): self;

    public function isConfidential(): bool;

    public function isPlainTextPkceAllowed(): bool;

    public function setAllowPlainTextPkce(bool $allowPlainTextPkce): self;

    public function newClientInstance(string $name, string $identifier, ?string $secret): self;
}
