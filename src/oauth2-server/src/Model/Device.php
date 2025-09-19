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
use DateTimeImmutable;
use FriendsOfHyperf\Oauth2\Server\Enums\DeviceCodeStatus;
use FriendsOfHyperf\Oauth2\Server\Model\Casts\ScopesCast;
use FriendsOfHyperf\Oauth2\Server\ValueObject\Scope;
use Hyperf\Database\Model\Relations\BelongsTo;
use Hyperf\DbConnection\Model\Model;

/**
 * @property string $user_code
 * @property string $device_code
 * @property string $user_id
 * @property string $client_id
 * @property array<Scope> $scopes
 * @property Carbon $expires_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property ClientInterface $client
 * @property string $ip_address
 * @property DeviceCodeStatus $status
 * @property null|Carbon $last_poll_at
 * @property bool $revoke
 */
class Device extends Model implements DeviceCodeInterface
{
    public bool $incrementing = false;

    protected ?string $table = 'oauth_device_grant';

    protected string $keyType = 'string';

    protected string $primaryKey = 'device_code';

    protected array $fillable = [
        'user_code', 'device_code', 'client_id', 'scopes', 'expires_at', 'status',
        'last_poll_at', 'ip_address', 'created_at', 'updated_at', 'verification_uri',
        'revoke',
    ];

    protected array $casts = [
        'scopes' => ScopesCast::class,
        'expires_at' => 'datetime',
        'last_poll_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'status' => DeviceCodeStatus::class,
        'ip_address' => 'string',
        'user_code' => 'string',
        'device_code' => 'string',
        'client_id' => 'string',
        'user_id' => 'string',
        'revoke' => 'bool',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id', 'id');
    }

    public function getClient(): ?ClientInterface
    {
        // @phpstan-ignore-next-line
        return $this->client()->first();
    }

    public function getDeviceCode(): string
    {
        return $this->device_code;
    }

    public function getUserIdentifier(): string
    {
        return $this->user_id;
    }

    public function getClientIdentifier(): string
    {
        return $this->client_id;
    }

    public function getScopes(): array
    {
        return $this->scopes;
    }

    public function getExpiry(): DateTimeImmutable
    {
        return $this->expires_at->toDateTimeImmutable();
    }

    public function getStatus(): DeviceCodeStatus
    {
        return $this->status;
    }

    public function getLastPoll(): ?DateTimeImmutable
    {
        return $this->last_poll_at?->toDateTimeImmutable();
    }

    public function getUserCode(): string
    {
        return $this->user_code;
    }

    public function getIpAddress(): ?string
    {
        return $this->ip_address;
    }

    public function setUserIdentifier(string $userIdentifier): DeviceCodeInterface
    {
        $this->user_id = $userIdentifier;
        return $this;
    }

    public function setClientIdentifier(string $clientIdentifier): DeviceCodeInterface
    {
        $this->client_id = $clientIdentifier;
        return $this;
    }

    public function setDeviceCode(string $deviceCode): DeviceCodeInterface
    {
        $this->device_code = $deviceCode;
        return $this;
    }

    public function setUserCode(string $userCode): DeviceCodeInterface
    {
        $this->user_code = $userCode;
        return $this;
    }

    public function setIpAddress(string $ipAddress): DeviceCodeInterface
    {
        $this->ip_address = $ipAddress;
        return $this;
    }

    public function setScopes(Scope ...$scopes): DeviceCodeInterface
    {
        $this->scopes = $scopes;
        return $this;
    }

    public function setExpiry(DateTimeImmutable $expiry): DeviceCodeInterface
    {
        $this->expires_at = Carbon::createFromImmutable($expiry);
        return $this;
    }

    public function setLastPoll(DateTimeImmutable $lastPoll): DeviceCodeInterface
    {
        $this->last_poll_at = Carbon::instance($lastPoll);
        return $this;
    }

    public function setStatus(DeviceCodeStatus $status): DeviceCodeInterface
    {
        $this->status = $status;
        return $this;
    }

    public function isRevoked(): bool
    {
        return $this->revoke;
    }

    public function revoke(): DeviceCodeInterface
    {
        $this->revoke = true;
        return $this;
    }
}
