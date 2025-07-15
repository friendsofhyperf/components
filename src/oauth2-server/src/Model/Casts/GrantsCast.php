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

use FriendsOfHyperf\Oauth2\Server\ValueObject\Grant;
use Hyperf\Codec\Json;
use Hyperf\Contract\CastsAttributes;

class GrantsCast implements CastsAttributes
{
    public function get($model, string $key, $value, array $attributes)
    {
        if (is_string($value)) {
            $value = Json::decode($value);
        }

        if (is_array($value)) {
            return array_map(fn ($grant) => new Grant($grant), $value);
        }

        return [];
    }

    public function set($model, string $key, $value, array $attributes)
    {
        if (is_array($value)) {
            $value = array_map(fn (Grant $grant) => (string) $grant, $value);
        } elseif ($value instanceof Grant) {
            $value = [(string) $value];
        }
        return Json::encode($value);
    }
}
