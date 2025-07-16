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

use Carbon\Carbon;
use FriendsOfHyperf\Oauth2\Server\Model\Casts\GrantsCast;
use FriendsOfHyperf\Oauth2\Server\Model\Casts\RedirectsCast;
use FriendsOfHyperf\Oauth2\Server\Model\Casts\ScopesCast;
use FriendsOfHyperf\Oauth2\Server\ValueObject\Grant;
use FriendsOfHyperf\Oauth2\Server\ValueObject\RedirectUri;
use FriendsOfHyperf\Oauth2\Server\ValueObject\Scope;
use Hyperf\Database\Model\Concerns\HasUuids;
use Hyperf\DbConnection\Model\Model;

/**
 * @property string $id
 * @property string $secret
 * @property string $name
 * @property RedirectUri[] $redirects
 * @property Grant[] $grants
 * @property Scope[] $scopes
 * @property bool $active
 * @property bool $allow_plain_text_pkce
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class Client extends Model implements ClientInterface
{
    use HasUuids;

    protected ?string $table = 'oauth_client';

    protected array $fillable = [
        'id', 'secret', 'name', 'redirects', 'grants', 'scopes', 'active', 'allow_plain_text_pkce',
    ];

    protected array $casts = [
        'id' => 'string',
        'secret' => 'string',
        'name' => 'string',
        'redirects' => RedirectsCast::class,
        'grants' => GrantsCast::class,
        'scopes' => ScopesCast::class,
        'active' => 'boolean',
        'allow_plain_text_pkce' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function getIdentifier(): string
    {
        return $this->getKey();
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getSecret(): ?string
    {
        return $this->secret;
    }

    public function getRedirectUris(): array
    {
        return $this->redirects;
    }

    public function setRedirectUris(RedirectUri ...$redirectUris): ClientInterface
    {
        $this->redirects = $redirectUris;
        return $this;
    }

    public function getGrants(): array
    {
        return $this->grants;
    }

    public function setGrants(Grant ...$grants): ClientInterface
    {
        $this->grants = $grants;
        return $this;
    }

    public function getScopes(): array
    {
        return $this->scopes;
    }

    public function setScopes(Scope ...$scopes): ClientInterface
    {
        $this->scopes = $scopes;
        return $this;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): ClientInterface
    {
        $this->active = $active;
        return $this;
    }

    public function isConfidential(): bool
    {
        return $this->secret !== null && $this->secret !== '';
    }

    public function isPlainTextPkceAllowed(): bool
    {
        return $this->allow_plain_text_pkce;
    }

    public function setAllowPlainTextPkce(bool $allowPlainTextPkce): ClientInterface
    {
        $this->allow_plain_text_pkce = $allowPlainTextPkce;
        return $this;
    }

    public function newClientInstance(string $name, string $identifier, ?string $secret): ClientInterface
    {
        return new static([
            'name' => $name,
            'id' => $identifier,
            'secret' => $secret,
        ]);
    }
}
