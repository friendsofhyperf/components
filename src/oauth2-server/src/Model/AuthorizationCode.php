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
use Hyperf\Database\Model\Relations\BelongsTo;
use Hyperf\DbConnection\Model\Model;
use RuntimeException;
use Stringable;

/**
 * @property string $code
 * @property string $user_id
 * @property string $client_id
 * @property array<Scope> $scopes
 * @property string $redirect_uri
 * @property bool $revoked
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon $expires_at
 * @property Client $client
 */
class AuthorizationCode extends Model implements Stringable, AuthorizationCodeInterface
{
    public bool $incrementing = false;

    protected ?string $table = 'oauth_authorization_code';

    protected string $primaryKey = 'code';

    protected string $keyType = 'string';

    protected array $fillable = [
        'code',
        'user_id',
        'client_id',
        'scopes',
        'revoked',
        'redirect_uri',
        'expires_at',
        'created_at',
        'updated_at',
    ];

    protected array $casts = [
        'code' => 'string',
        'user_id' => 'string',
        'client_id' => 'string',
        'scopes' => ScopesCast::class,
        'redirect_uri' => 'string',
        'expires_at' => 'datetime',
        'revoked' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function __toString(): string
    {
        return $this->getIdentifier();
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id', 'id');
    }

    public function getIdentifier(): string
    {
        return $this->code;
    }

    public function getExpiryDateTime(): DateTimeInterface
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

    public function revoke(): AuthorizationCodeInterface
    {
        $this->revoked = true;

        return $this;
    }
}
