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

use DateTimeImmutable;
use FriendsOfHyperf\Oauth2\Server\Enums\DeviceCodeStatus;
use FriendsOfHyperf\Oauth2\Server\ValueObject\Scope;

interface DeviceCodeInterface
{
    public function getDeviceCode(): string;

    public function getUserIdentifier(): string;

    public function getClientIdentifier(): string;

    public function getScopes(): array;

    public function getClient(): ?ClientInterface;

    public function getExpiry(): DateTimeImmutable;

    public function getStatus(): DeviceCodeStatus;

    public function getLastPoll(): ?DateTimeImmutable;

    public function getUserCode(): string;

    public function getIpAddress(): ?string;

    public function setUserIdentifier(string $userIdentifier): self;

    public function setClientIdentifier(string $clientIdentifier): self;

    public function setDeviceCode(string $deviceCode): self;

    public function setUserCode(string $userCode): self;

    public function setIpAddress(string $ipAddress): self;

    public function setScopes(Scope ...$scopes): self;

    public function setExpiry(DateTimeImmutable $expiry): self;

    public function setLastPoll(DateTimeImmutable $lastPoll): self;

    public function setStatus(DeviceCodeStatus $status): self;

    public function isRevoked(): bool;

    public function revoke(): self;
}
