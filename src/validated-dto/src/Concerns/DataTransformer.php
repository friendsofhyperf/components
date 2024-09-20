<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\ValidatedDTO\Concerns;

use Hyperf\Database\Model\Model;

trait DataTransformer
{
    public function toArray(): array
    {
        return $this->buildDataForExport();
    }

    public function toJson(int $options = 0): string
    {
        return json_encode($this->buildDataForExport(), $options);
    }

    public function toPrettyJson(): string
    {
        return $this->toJson(JSON_PRETTY_PRINT);
    }

    /**
     * @param class-string<Model> $model
     */
    public function toModel(string $model): Model
    {
        return new $model($this->buildDataForExport());
    }
}
