<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.x/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\ValidatedDTO\Casting;

use Hyperf\Utils\Collection;

class CollectionCast implements Castable
{
    public function __construct(private ?Castable $type = null)
    {
    }

    public function cast(string $property, mixed $value): Collection
    {
        $arrayCast = new ArrayCast();
        $value = $arrayCast->cast($property, $value);

        return Collection::make($value)
            ->when(! is_null($this->type), function ($collection) use ($property) {
                return $collection->map(fn ($item) => $this->type->cast($property, $item));
            });
    }
}
