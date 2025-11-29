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
use Hyperf\Database\Model\Concerns\HasUuids;
use Hyperf\Database\Model\Relations\BelongsTo;
use Hyperf\DbConnection\Model\Model;
use Stringable;

/**
 * @property string $id
 * @property string $access_token_id
 * @property bool $revoked
 * @property Carbon $expires_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property AccessToken $access_token
 */
class RefreshToken extends Model implements Stringable, RefreshTokenInterface
{
    use HasUuids;

    protected ?string $table = 'oauth_refresh_token';

    protected array $fillable = [
        'id', 'access_token_id', 'revoked', 'expires_at', 'created_at', 'updated_at',
    ];

    protected array $casts = [
        'id' => 'string',
        'access_token_id' => 'string',
        'expires_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function __toString(): string
    {
        return $this->getIdentifier();
    }

    public function accessToken(): BelongsTo
    {
        return $this->belongsTo(AccessToken::class, 'access_token_id', 'id');
    }

    public function getIdentifier(): string
    {
        return $this->id;
    }

    public function getExpiry(): DateTimeInterface
    {
        return $this->expires_at;
    }

    public function getAccessToken(): ?AccessTokenInterface
    {
        return $this->accessToken()->first(); // @phpstan-ignore-line
    }

    public function isRevoked(): bool
    {
        return $this->revoked;
    }

    public function revoke(): RefreshTokenInterface
    {
        $this->revoked = true;

        return $this;
    }
}
