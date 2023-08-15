<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\ValidatedDTO\Casting;

use FriendsOfHyperf\ValidatedDTO\Exception\CastException;
use FriendsOfHyperf\ValidatedDTO\Exception\CastTargetException;
use Hyperf\Database\Model\Model;
use Throwable;

class ModelCast implements Castable
{
    public function __construct(private string $modelClass)
    {
    }

    /**
     * @throws CastException|CastTargetException
     */
    public function cast(string $property, mixed $value): Model
    {
        if (is_string($value)) {
            $value = json_decode($value, true);
        }

        if (! is_array($value)) {
            throw new CastException($property);
        }

        try {
            $model = new $this->modelClass($value);
        } catch (Throwable) {
            throw new CastTargetException($property);
        }

        if (! $model instanceof Model) {
            throw new CastTargetException($property);
        }

        return $model;
    }
}
