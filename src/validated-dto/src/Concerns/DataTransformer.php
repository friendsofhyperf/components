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

    public function toJson(): string
    {
        return json_encode($this->buildDataForExport());
    }

    public function toPrettyJson(): string
    {
        return json_encode($this->buildDataForExport(), JSON_PRETTY_PRINT);
    }

    public function toModel(string $model): Model
    {
        return new $model($this->buildDataForExport());
    }
}
