<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Oauth2\Server\Model\Casts;

use FriendsOfHyperf\Oauth2\Server\ValueObject\Scope;
use Hyperf\Codec\Json;
use Hyperf\Contract\CastsAttributes;

class ScopesCast implements CastsAttributes
{
    public function get($model, string $key, $value, array $attributes)
    {
        if (is_string($value)) {
            $value = Json::decode($value);
        }

        if (is_array($value)) {
            return array_map(fn ($scope) => new Scope($scope), $value);
        }

        return [];
    }

    public function set($model, string $key, $value, array $attributes)
    {
        if (is_array($value)) {
            $value = array_map(fn (Scope $scope) => (string) $scope, $value);
        } elseif ($value instanceof Scope) {
            $value = (string) $value;
        }
        return Json::encode($value);
    }
}
