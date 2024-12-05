<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Purifier\Casts;

use Hyperf\Contract\CastsAttributes;
use Hyperf\Database\Model\Model;

use function FriendsOfHyperf\Purifier\clean;

class CleanHtmlOutput implements CastsAttributes
{
    use WithConfig;

    /**
     * Clean the HTML when casting the given value.
     *
     * @param Model $model
     * @param mixed $value
     */
    public function get($model, string $key, $value, array $attributes): mixed
    {
        return clean($value, $this->config);
    }

    /**
     * Prepare the given value for storage by cleaning the HTML.
     *
     * @param Model $model
     * @param mixed $value
     */
    public function set($model, string $key, $value, array $attributes): array|string
    {
        return $value;
    }
}
