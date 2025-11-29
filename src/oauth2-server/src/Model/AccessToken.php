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
use DateTimeInterface;
use FriendsOfHyperf\Oauth2\Server\Model\Casts\ScopesCast;
use FriendsOfHyperf\Oauth2\Server\ValueObject\Scope;
use Hyperf\Database\Model\Concerns\HasUuids;
use Hyperf\Database\Model\Relations\BelongsTo;
use Hyperf\DbConnection\Model\Model;
use RuntimeException;
use Stringable;

/**
 * @property string $id
 * @property string $user_id
 * @property array<Scope> $scopes
 * @property string $client_id
 * @property bool $revoked
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon $expires_at
 * @property Client $client
 */
class AccessToken extends Model implements Stringable, AccessTokenInterface
{
    use HasUuids;

    protected ?string $table = 'oauth_access_token';

    protected array $fillable = [
        'id',
        'user_id',
        'client_id',
        'scopes',
        'revoked',
        'created_at',
        'updated_at',
        'expires_at',
    ];

    protected array $casts = [
        'scopes' => ScopesCast::class,
        'revoked' => 'boolean',
        'expires_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'user_id' => 'string',
        'client_id' => 'string',
        'id' => 'string',
    ];

    public function __toString(): string
    {
        return $this->getIdentifier();
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function getIdentifier(): string
    {
        return $this->getKey();
    }

    public function getExpiry(): DateTimeInterface
    {
        return $this->expires_at->toDateTimeImmutable();
    }

    public function getUserIdentifier(): ?string
    {
        return $this->user_id;
    }

    public function getClient(): ClientInterface
    {
        /**
         * @var null|ClientInterface $client
         */
        $client = $this->client()->first();
        if ($client === null) {
            throw new RuntimeException('Access token has no associated client');
        }
        return $client;
    }

    public function getScopes(): array
    {
        return $this->scopes;
    }

    public function isRevoked(): bool
    {
        return $this->revoked;
    }

    public function revoke(): AccessTokenInterface
    {
        $this->revoked = true;

        return $this;
    }
}
