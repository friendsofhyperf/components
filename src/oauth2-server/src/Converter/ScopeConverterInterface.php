<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Oauth2\Server\Converter;

use FriendsOfHyperf\Oauth2\Server\Entity\Scope as ScopeEntity;
use FriendsOfHyperf\Oauth2\Server\ValueObject\Scope as ScopeModel;
use League\OAuth2\Server\Entities\ScopeEntityInterface;

interface ScopeConverterInterface
{
    public function toDomain(ScopeEntityInterface $scope): ScopeModel;

    /**
     * @param list<ScopeEntityInterface> $scopes
     *
     * @return list<ScopeModel>
     */
    public function toDomainArray(array $scopes): array;

    public function toLeague(ScopeModel $scope): ScopeEntity;

    /**
     * @param list<ScopeModel> $scopes
     *
     * @return list<ScopeEntity>
     */
    public function toLeagueArray(array $scopes): array;
}
